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
            'tick_interval' => '毎日 04:00 UTC にターン解決',
            'max_instruction_per_day' => '1命令 / ユーザー',
            'instruction_timeout' => '1ターン（未実行の場合は破棄）',
            'world_update_mode' => '差分コミット（イベントログ付き）',
            'failure_policy' => '不正命令は No-Op として記録',
        ];

        $instructionSet = [
            ['opcode' => 'SCAN', 'args' => 'target, radius', 'effect' => '周囲を観測し、センサー結果をログに追加'],
            ['opcode' => 'MOVE', 'args' => 'direction, steps', 'effect' => 'ロボットの座標を変更。障害物に衝突した場合は停止'],
            ['opcode' => 'INTERACT', 'args' => 'object_id', 'effect' => '対象オブジェクトに対し文脈依存アクションを実行'],
            ['opcode' => 'COMMIT', 'args' => 'flag_key, value', 'effect' => '探索で得た仮説を章進行フラグとして確定申請'],
        ];

        return $this->render('story_vm/manual.html.twig', [
            'vm_settings' => $vmSettings,
            'instruction_set' => $instructionSet,
        ]);
    }
}
