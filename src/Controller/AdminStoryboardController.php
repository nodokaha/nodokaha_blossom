<?php

namespace App\Controller;

use App\Service\StoryVmStateService;
use App\Service\StoryboardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminStoryboardController extends AbstractController
{
    #[Route('/admin/storyboard/setting', name: 'app_admin_storyboard_setting', methods: ['GET', 'POST'])]
    public function setting(Request $request, StoryVmStateService $storyVmStateService, StoryboardService $storyboardService): Response
    {
        $state = $storyVmStateService->loadState();
        $world = is_array($state['world'] ?? null) ? $state['world'] : [];

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('admin_storyboard_setting', (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('不正なリクエストです。');
            }

            $challenge = $storyboardService->buildChallenge(
                (string) $request->request->get('period', ''),
                (string) $request->request->get('title', ''),
                (string) $request->request->get('objective', '')
            );

            $world = $storyboardService->applyChallengeToWorld($world, $challenge);

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
