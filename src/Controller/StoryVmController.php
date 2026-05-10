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

        $todayMission = [
            'chapter' => 'Chapter 01: ひかりの庭',
            'goal' => '少女ロボットが花の反応を見つけるため、未観測エリアを3セル以上探索する',
            'hint' => '地形の変化ログと他プレイヤーの観測ログを突き合わせると、温度勾配に偏りがあります。',
        ];

        $recentLogs = [
            ['time' => '06:10', 'actor' => 'player_A', 'effect' => '北西区画の霧密度が低下'],
            ['time' => '08:40', 'actor' => 'player_K', 'effect' => '観測ビーコン#12の電力が回復'],
            ['time' => '12:00', 'actor' => 'system', 'effect' => 'ターン更新: 地形シードが1段階遷移'],
        ];

        return $this->render('story_vm/index.html.twig', [
            'daily_token_limit' => $dailyTokenLimit,
            'today_mission' => $todayMission,
            'recent_logs' => $recentLogs,
        ]);
    }
}
