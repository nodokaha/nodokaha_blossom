<?php

namespace App\Controller;

use App\Service\StoryVmService;
use App\Service\StoryVmStateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StoryVmController extends AbstractController
{
    public function __construct(
        private StoryVmService $storyVmService,
        private StoryVmStateService $storyVmStateService,
    ) {}

    #[Route('/', name: 'story_vm_home')]
    public function index(): Response
    {
        $dailyTokenLimit = 1;

        return $this->render('story_vm/index.html.twig', [
            'daily_token_limit' => $dailyTokenLimit,
        ]);
    }

    #[Route('/vm-manual', name: 'story_vm_manual')]
    public function manual(): Response
    {
        $vmSettings = [
            'machine_model' => 'SECD派生（Stack / Environment / Control / Dump）',
            'tick_interval' => '毎日 04:00 UTC にターン解決',
            'max_instruction_per_day' => '1命令 / ユーザー識別子',
            'instruction_timeout' => '命令は蓄積可能、実行は任意タイミング',
            'failure_policy' => '不正命令は No-Op として記録',
        ];

        $instructionSet = [
            ['opcode' => 'LDC', 'args' => 'value', 'effect' => 'Stackに即値をpush'],
            ['opcode' => 'LD', 'args' => 'name', 'effect' => 'Environment[name]をStackへpush'],
            ['opcode' => 'ST', 'args' => 'name', 'effect' => 'StackトップをEnvironment[name]へ保存'],
            ['opcode' => 'ADD', 'args' => '-', 'effect' => 'Stackの2値を加算してpush'],
            ['opcode' => 'SUB', 'args' => '-', 'effect' => 'Stackの2値で減算してpush'],
            ['opcode' => 'MUL', 'args' => '-', 'effect' => 'Stackの2値を乗算してpush'],
            ['opcode' => 'DIV', 'args' => '-', 'effect' => 'Stackの2値で除算してpush（0除算はNo-Op）'],
            ['opcode' => 'SEL', 'args' => 'then_label, else_label', 'effect' => '条件分岐（0ならelse）'],
            ['opcode' => 'JOIN', 'args' => '-', 'effect' => 'Dumpから制御復帰'],
            ['opcode' => 'STOP', 'args' => '-', 'effect' => '実行停止'],
        ];

        return $this->render('story_vm/manual.html.twig', [
            'vm_settings' => $vmSettings,
            'instruction_set' => $instructionSet,
        ]);
    }

    #[Route('/vm-lab', name: 'story_vm_lab', methods: ['GET', 'POST'])]
    public function lab(Request $request): Response
    {
        $state = $this->storyVmStateService->loadState();
        $userId = (string) $request->cookies->get('vm_user_id', substr(sha1((string) $request->getClientIp()), 0, 8));
        $today = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d');

        $message = null;

        if ($request->isMethod('POST')) {
            $action = (string) $request->request->get('action');

            if ($action === 'add_instruction') {
                $opcode = strtoupper(trim((string) $request->request->get('opcode')));
                $args = trim((string) $request->request->get('args'));

                $lastDate = $state['last_insert_date'][$userId] ?? null;
                if ($lastDate === $today) {
                    $message = '今日はすでに1命令を積んでいます。次は明日追加できます。';
                } elseif ($opcode === '') {
                    $message = 'Opcodeは必須です。';
                } else {
                    $state['program'][] = ['opcode' => $opcode, 'args' => $args, 'by' => $userId, 'date' => $today];
                    $state['last_insert_date'][$userId] = $today;
                    $state['world']['chronicle'][] = sprintf('Day %d: %s が %s %s を投入', (int) ($state['world']['day'] ?? 1), $userId, $opcode, $args);
                    $message = sprintf('命令 %s を積みました。', $opcode);
                }
            }

            if ($action === 'load_punchcard') {
                $file = basename((string) $request->request->get('punchcard_file'));
                $path = __DIR__.'/../../data/punchcards/'.$file;
                if (is_file($path)) {
                    $rows = json_decode((string) file_get_contents($path), true);
                    if (is_array($rows)) {
                        $state['program'] = [];
                        foreach ($rows as $row) {
                            $state['program'][] = [
                                'opcode' => strtoupper((string) ($row['opcode'] ?? '')),
                                'args' => (string) ($row['args'] ?? ''),
                                'by' => 'punchcard',
                                'date' => $today,
                            ];
                        }
                        $state['world']['chronicle'][] = 'パンチコードを投入、協力シーケンスを更新';
                        $message = 'パンチコードを読み込みました。';
                    }
                }
            }

            if ($action === 'run') {
                $state['run_result'] = $this->storyVmService->runProgram($state['program']);
                $state = $this->resolveWorldTurn($state);
                $message = 'プログラムを実行し、箱庭ターンを進めました。';
            }

            $this->storyVmStateService->saveState($state);
        }

        $punchcards = glob(__DIR__.'/../../data/punchcards/*.json') ?: [];
        if (empty($state['world']['field']['tiles'])) {
            $state['world']['field'] = $this->buildField($state['world'], $state['run_result']['env'] ?? []);
        }

        $response = $this->render('story_vm/lab.html.twig', [
            'state' => $state,
            'message' => $message,
            'today' => $today,
            'punchcards' => array_map('basename', $punchcards),
        ]);
        $response->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie('vm_user_id', $userId, strtotime('+1 year')));

        return $response;
    }

    private function resolveWorldTurn(array $state): array
    {
        $env = $state['run_result']['env'] ?? [];
        $world = $state['world'];

        $light = (float) ($env['light'] ?? 0);
        $water = (float) ($env['water'] ?? 0);
        $care = max(0, min(20, ($light + $water) * 2));

        $world['day'] = ((int) ($world['day'] ?? 1)) + 1;
        $world['biome']['bloom_rate'] = max(0, min(100, (int) ($world['biome']['bloom_rate'] ?? 0) + (int) round($care - 3)));
        $world['biome']['energy'] = max(0, min(20, (int) ($world['biome']['energy'] ?? 0) + (int) round(($light - $water) / 2)));
        $world['biome']['weather'] = $world['biome']['energy'] > 8 ? 'sunny' : ($world['biome']['energy'] < 3 ? 'rain' : 'mist');
        $world['npcs']['caretaker_ai'] = $world['biome']['bloom_rate'] >= 20 ? '開花同期モード' : '巡回補助モード';
        $world['field'] = $this->buildField($world, $env);

        $world['chronicle'][] = sprintf(
            'Day %d 解決: bloom=%d%%, weather=%s, caretaker=%s',
            $world['day'],
            $world['biome']['bloom_rate'],
            $world['biome']['weather'],
            $world['npcs']['caretaker_ai']
        );

        $world['chronicle'] = array_slice($world['chronicle'], -10);
        $state['world'] = $world;

        return $state;
    }

    private function buildField(array $world, array $env): array
    {
        $width = max(4, (int) ($world['field']['width'] ?? 12));
        $height = max(4, (int) ($world['field']['height'] ?? 8));
        $bloomRate = (int) ($world['biome']['bloom_rate'] ?? 0);
        $energy = (int) ($world['biome']['energy'] ?? 0);
        $light = (float) ($env['light'] ?? 0);
        $water = (float) ($env['water'] ?? 0);
        $day = (int) ($world['day'] ?? 1);
        $seed = max(1, $day + $bloomRate + ($energy * 3));

        $tiles = [];
        for ($y = 0; $y < $height; $y++) {
            $row = [];
            for ($x = 0; $x < $width; $x++) {
                $noise = ($x * 17 + $y * 31 + $seed * 13) % 100;
                $moisture = ($water * 10) + (($x + $y + $day) % 5) * 4;
                $sun = ($light * 10) + (($x * 2 + $y) % 7) * 3;

                $terrain = 'soil';
                if ($noise < 10 && $moisture > $sun) {
                    $terrain = 'water';
                } elseif ($noise > 80 && $sun > $moisture) {
                    $terrain = 'rock';
                }

                $growthScore = $bloomRate + $sun + $moisture - abs($sun - $moisture);
                $growth = 'seed';
                if ($terrain === 'water') {
                    $growth = 'pond';
                } elseif ($terrain === 'rock') {
                    $growth = 'moss';
                } elseif ($growthScore > 110) {
                    $growth = 'bloom';
                } elseif ($growthScore > 80) {
                    $growth = 'sprout';
                }

                $row[] = [
                    'x' => $x,
                    'y' => $y,
                    'terrain' => $terrain,
                    'growth' => $growth,
                ];
            }
            $tiles[] = $row;
        }

        return [
            'width' => $width,
            'height' => $height,
            'tiles' => $tiles,
        ];
    }
}
