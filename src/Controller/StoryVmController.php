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
        $vmConfig = [
            'daily_token_limit' => 1,
            'tick_interval' => '毎日 04:00 UTC',
            'max_program_length' => 8,
            'memory_slots' => 16,
            'stack_limit' => 8,
            'energy_per_turn' => 12,
        ];

        return $this->render('story_vm/index.html.twig', [
            'vm_config' => $vmConfig,
        ]);
    }

    #[Route('/manual', name: 'story_vm_manual')]
    public function manual(): Response
    {
        $instructionSet = [
            ['op' => 'SCAN dir', 'cost' => 1, 'desc' => '周辺タイルを探索し、地形とオブジェクト情報を取得する。'],
            ['op' => 'MOVE dir', 'cost' => 2, 'desc' => '指定方向に移動する。障害物がある場合は失敗ログが残る。'],
            ['op' => 'MARK key value', 'cost' => 1, 'desc' => 'メモリスロットへ観測結果を書き込み、次ターンへ保持する。'],
            ['op' => 'ASK npc topic', 'cost' => 2, 'desc' => 'NPCと対話し、ヒントまたは条件フラグを得る。'],
            ['op' => 'WAIT', 'cost' => 0, 'desc' => '行動を保留し、エネルギーを温存する。'],
        ];

        return $this->render('story_vm/manual.html.twig', [
            'instruction_set' => $instructionSet,
        ]);
    }
}
