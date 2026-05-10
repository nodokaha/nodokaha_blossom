<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StoryVmController extends AbstractController
{
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
            'vm_model' => 'SECDベース（Stack / Environment / Control / Dump）',
            'tick_interval' => '毎日 04:00 UTC にターン解決',
            'max_instruction_per_day' => '1命令 / ユーザー / 日',
            'instruction_timeout' => '1ターン（未実行の場合は翌日に持ち越し）',
            'memory_model' => 'セルメモリ（consセル） + シンボル表',
            'failure_policy' => '不正命令は No-Op として記録',
        ];

        $instructionSet = [
            ['opcode' => 'LDC', 'args' => 'value', 'effect' => '定数を Stack に push'],
            ['opcode' => 'LD', 'args' => '(frame,index)', 'effect' => 'Environment から値を読み込み push'],
            ['opcode' => 'ADD', 'args' => '-', 'effect' => 'Stack の上位2値を加算して push'],
            ['opcode' => 'SUB', 'args' => '-', 'effect' => 'Stack の上位2値を減算して push'],
            ['opcode' => 'MUL', 'args' => '-', 'effect' => 'Stack の上位2値を乗算して push'],
            ['opcode' => 'DIV', 'args' => '-', 'effect' => 'Stack の上位2値を除算して push（0除算はNo-Op）'],
            ['opcode' => 'CONS', 'args' => '-', 'effect' => '2値から cons セルを生成しメモリへ格納'],
            ['opcode' => 'CAR', 'args' => '-', 'effect' => 'ペア先頭要素を push'],
            ['opcode' => 'CDR', 'args' => '-', 'effect' => 'ペア後尾要素を push'],
            ['opcode' => 'SEL', 'args' => 'true_label,false_label', 'effect' => '条件分岐し Control を更新'],
            ['opcode' => 'JOIN', 'args' => '-', 'effect' => 'Dump から分岐復帰'],
            ['opcode' => 'STOP', 'args' => '-', 'effect' => '実行を停止し結果を確定'],
        ];

        $punchcards = [
            [
                'id' => 'pc_flower_scan',
                'name' => 'flower_scan.pcd',
                'description' => '周辺を数値化し閾値比較する探索ルーチン',
                'program' => ['LDC 8', 'LDC 13', 'ADD', 'LDC 2', 'DIV', 'STOP'],
            ],
            [
                'id' => 'pc_signal_pair',
                'name' => 'signal_pair.pcd',
                'description' => '観測値のペアを cons で構築して保持',
                'program' => ['LDC 1', 'LDC 99', 'CONS', 'CAR', 'STOP'],
            ],
            [
                'id' => 'pc_energy_calc',
                'name' => 'energy_calc.pcd',
                'description' => '必要エネルギーを積算して実行可否を判定',
                'program' => ['LDC 4', 'LDC 6', 'MUL', 'LDC 3', 'SUB', 'STOP'],
            ],
        ];

        return $this->render('story_vm/manual.html.twig', [
            'vm_settings' => $vmSettings,
            'instruction_set' => $instructionSet,
            'punchcards' => $punchcards,
        ]);
    }
}
