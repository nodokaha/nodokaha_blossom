<?php

namespace App\Controller;

use App\Repository\WeeklyExecutionRepository;
use App\Service\WorldStateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/execution', name: 'app_admin_execution_')]
final class AdminExecutionController extends AbstractController
{
    #[Route('/schedule', name: 'schedule', methods: ['GET'])]
    public function schedule(
        WorldStateService $worldStateService,
        WeeklyExecutionRepository $executionRepository,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $worldState = $worldStateService->getWorldState();
        $nextExecution = $executionRepository->findLatest();

        return $this->render('admin/execution_schedule.html.twig', [
            'current_week' => $worldState->getCurrentWeek(),
            'current_day' => $worldState->getCurrentDay(),
            'next_execution' => $nextExecution,
        ]);
    }

    #[Route('/log/{weekNumber}', name: 'log_view', methods: ['GET'])]
    public function logView(
        int $weekNumber,
        WeeklyExecutionRepository $executionRepository,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $execution = $executionRepository->findByWeekNumber($weekNumber);

        if (!$execution) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/execution_log.html.twig', [
            'execution' => $execution,
        ]);
    }

    #[Route('/recent', name: 'recent', methods: ['GET'])]
    public function recentExecutions(
        WeeklyExecutionRepository $executionRepository,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $executions = $executionRepository->findRecentExecutions(20);

        return $this->render('admin/execution_list.html.twig', [
            'executions' => $executions,
        ]);
    }
}
