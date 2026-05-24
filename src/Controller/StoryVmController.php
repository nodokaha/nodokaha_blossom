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
    private const ALLOWED_OPCODES = ['LDC', 'LD', 'ST', 'ADD', 'SUB', 'MUL', 'DIV', 'SEL', 'JOIN', 'BROADCAST', 'INFLUENCE', 'STOP'];

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
            'online_interference' => 'INFLUENCE/BROADCASTで他ユーザー箱庭へ干渉',
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
            ['opcode' => 'BROADCAST', 'args' => 'channel, impact', 'effect' => '全体同期値へ加算し、全箱庭に波及'],
            ['opcode' => 'INFLUENCE', 'args' => 'target_email_or_all, impact', 'effect' => '対象ユーザー箱庭へ影響を蓄積'],
            ['opcode' => 'STOP', 'args' => '-', 'effect' => '実行停止'],
        ];

        return $this->render('story_vm/manual.html.twig', [
            'vm_settings' => $vmSettings,
            'instruction_set' => $instructionSet,
        ]);
    }

    #[Route('/admin/vm', name: 'story_vm_admin', methods: ['GET', 'POST'])]
    public function admin(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $state = $this->storyVmStateService->loadState();
        $message = null;

        if ($request->isMethod('POST')) {
            $title = trim((string) $request->request->get('title'));
            $objective = trim((string) $request->request->get('objective'));
            $period = trim((string) $request->request->get('period'));

            if ($title === '' || $objective === '' || $period === '') {
                $message = 'period / title / objective はすべて必須です。';
            } else {
                $state['world']['semiannual_challenges'] = array_values(array_filter(
                    $state['world']['semiannual_challenges'] ?? [],
                    static fn (array $challenge): bool => (string) ($challenge['period'] ?? '') !== $period
                ));
                $state['world']['semiannual_challenges'][] = [
                    'period' => $period,
                    'title' => $title,
                    'objective' => $objective,
                ];
                $state['world']['chronicle'][] = sprintf('運営更新: 半年課題 %s を登録 (%s)', $period, $title);
                $message = sprintf('半年課題 %s を登録しました。', $period);
                $state = $this->applySemiannualChallenge($state);
                $this->storyVmStateService->saveState($state);
            }
        }

        return $this->render('story_vm/admin.html.twig', [
            'state' => $state,
            'message' => $message,
            'active_period' => $this->resolveCurrentPeriodKey($state),
        ]);
    }

    private function applySemiannualChallenge(array $state): array
    {
        $period = $this->resolveCurrentPeriodKey($state);
        foreach (($state['world']['semiannual_challenges'] ?? []) as $challenge) {
            if (($challenge['period'] ?? '') !== $period) {
                continue;
            }
            $state['world']['chapter'] = (string) ($challenge['title'] ?? $state['world']['chapter']);
            $state['world']['objective'] = (string) ($challenge['objective'] ?? $state['world']['objective']);
            break;
        }

        return $state;
    }

    private function resolveCurrentPeriodKey(array $state): string
    {
        $start = (string) ($state['world']['calendar_start'] ?? '2026-01-01');
        try {
            $startDate = new \DateTimeImmutable($start, new \DateTimeZone('UTC'));
        } catch (\Throwable) {
            $startDate = new \DateTimeImmutable('2026-01-01', new \DateTimeZone('UTC'));
        }

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $days = max(0, (int) $startDate->diff($now)->format('%a'));
        $slot = intdiv($days, 182);
        $year = 2026 + intdiv($slot, 2);
        $half = ($slot % 2) === 0 ? 'H1' : 'H2';

        return sprintf('%d-%s', $year, $half);
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

        $network = is_array($world['network'] ?? null) ? $world['network'] : ['global_sync' => 0, 'garden_influence' => []];
        $network['global_sync'] = (int) ($network['global_sync'] ?? 0);
        $network['garden_influence'] = is_array($network['garden_influence'] ?? null) ? $network['garden_influence'] : [];

        foreach (($state['run_result']['network_signals'] ?? []) as $signal) {
            if (($signal['type'] ?? '') === 'broadcast') {
                $network['global_sync'] = max(-100, min(100, $network['global_sync'] + (int) ($signal['impact'] ?? 0)));
                continue;
            }

            if (($signal['type'] ?? '') === 'target') {
                $target = (string) ($signal['target'] ?? 'all');
                $impact = (int) ($signal['impact'] ?? 0);
                if ($target === 'all') {
                    $network['garden_influence']['all'] = (int) ($network['garden_influence']['all'] ?? 0) + $impact;
                } else {
                    $network['garden_influence'][$target] = (int) ($network['garden_influence'][$target] ?? 0) + $impact;
                }
            }
        }

        $world['network'] = $network;
        $world['field'] = $this->buildField($world, $env);

        $world['chronicle'][] = sprintf(
            'Day %d 解決: bloom=%d%%, weather=%s, caretaker=%s, sync=%d',
            $world['day'],
            $world['biome']['bloom_rate'],
            $world['biome']['weather'],
            $world['npcs']['caretaker_ai'],
            (int) ($world['network']['global_sync'] ?? 0)
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
