<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StoryVmController extends AbstractController
{
    private const STATE_FILE = __DIR__.'/../../var/story_vm_state.json';

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
        $state = $this->loadState();
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
                        $message = 'パンチコードを読み込みました。';
                    }
                }
            }

            if ($action === 'run') {
                $state['run_result'] = $this->runProgram($state['program']);
                $message = 'プログラムを実行しました。';
            }

            $this->saveState($state);
        }

        $punchcards = glob(__DIR__.'/../../data/punchcards/*.json') ?: [];

        $response = $this->render('story_vm/lab.html.twig', [
            'state' => $state,
            'message' => $message,
            'today' => $today,
            'punchcards' => array_map('basename', $punchcards),
        ]);
        $response->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie('vm_user_id', $userId, strtotime('+1 year')));

        return $response;
    }

    private function runProgram(array $program): array
    {
        $stack = [];
        $env = [];
        $dump = [];
        $pc = 0;
        $trace = [];

        while ($pc < count($program)) {
            $ins = $program[$pc];
            $opcode = strtoupper((string) ($ins['opcode'] ?? ''));
            $argText = (string) ($ins['args'] ?? '');
            $args = array_map('trim', $argText === '' ? [] : explode(',', $argText));

            switch ($opcode) {
                case 'LDC':
                    $stack[] = is_numeric($args[0] ?? null) ? (float) $args[0] : ($args[0] ?? null);
                    break;
                case 'LD':
                    $stack[] = $env[$args[0] ?? ''] ?? null;
                    break;
                case 'ST':
                    if ($args[0] ?? false) {
                        $env[$args[0]] = array_pop($stack);
                    }
                    break;
                case 'ADD':
                case 'SUB':
                case 'MUL':
                case 'DIV':
                    $b = array_pop($stack);
                    $a = array_pop($stack);
                    if (!is_numeric($a) || !is_numeric($b)) {
                        break;
                    }
                    if ($opcode === 'ADD') {$stack[] = $a + $b;}
                    if ($opcode === 'SUB') {$stack[] = $a - $b;}
                    if ($opcode === 'MUL') {$stack[] = $a * $b;}
                    if ($opcode === 'DIV' && (float) $b !== 0.0) {$stack[] = $a / $b;}
                    break;
                case 'SEL':
                    $cond = array_pop($stack);
                    $then = (int) ($args[0] ?? $pc + 1);
                    $else = (int) ($args[1] ?? $pc + 1);
                    $dump[] = $pc + 1;
                    $pc = ($cond ? $then : $else) - 1;
                    break;
                case 'JOIN':
                    $pc = ((int) array_pop($dump)) - 1;
                    break;
                case 'STOP':
                    $pc = count($program);
                    break;
                default:
                    break;
            }

            $trace[] = ['pc' => $pc + 1, 'opcode' => $opcode, 'stack' => $stack, 'env' => $env, 'dump' => $dump];
            $pc++;
        }

        return ['stack' => $stack, 'env' => $env, 'dump' => $dump, 'trace' => $trace];
    }

    private function loadState(): array
    {
        if (!is_file(self::STATE_FILE)) {
            return ['program' => [], 'last_insert_date' => [], 'run_result' => null];
        }

        $data = json_decode((string) file_get_contents(self::STATE_FILE), true);

        return is_array($data) ? $data : ['program' => [], 'last_insert_date' => [], 'run_result' => null];
    }

    private function saveState(array $state): void
    {
        if (!is_dir(dirname(self::STATE_FILE))) {
            mkdir(dirname(self::STATE_FILE), 0777, true);
        }
        file_put_contents(self::STATE_FILE, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
