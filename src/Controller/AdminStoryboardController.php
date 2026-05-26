<?php

namespace App\Controller;

use App\Service\StoryVmStateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminStoryboardController extends AbstractController
{
    #[Route('/admin/storyboard/setting', name: 'app_admin_storyboard_setting', methods: ['GET', 'POST'])]
    public function setting(Request $request, StoryVmStateService $storyVmStateService): Response
    {
        $state = $storyVmStateService->loadState();
        $world = is_array($state['world'] ?? null) ? $state['world'] : [];

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('admin_storyboard_setting', (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('不正なリクエストです。');
            }

            $period = trim((string) $request->request->get('period', ''));
            $title = trim((string) $request->request->get('title', ''));
            $objective = trim((string) $request->request->get('objective', ''));

            $challenge = [
                'period' => $period !== '' ? $period : date('Y').'-H1',
                'title' => $title !== '' ? $title : '無題チャプター',
                'objective' => $objective !== '' ? $objective : '目的未設定',
            ];

            $world['chapter'] = $challenge['title'];
            $world['objective'] = $challenge['objective'];

            $challenges = is_array($world['semiannual_challenges'] ?? null) ? $world['semiannual_challenges'] : [];
            $challenges[] = $challenge;
            $world['semiannual_challenges'] = array_slice($challenges, -12);

            $state['world'] = $world;
            $storyVmStateService->saveState($state);

            $this->addFlash('success', 'ストーリーボードを更新しました。');

            return $this->redirectToRoute('app_admin_storyboard_setting');
        }

        return $this->render('admin/storyboard_setting.html.twig', [
            'world' => $world,
            'semiannualChallenges' => is_array($world['semiannual_challenges'] ?? null) ? $world['semiannual_challenges'] : [],
        ]);
    }
}
