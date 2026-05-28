<?php

namespace App\Controller;

use App\Repository\WeeklyExecutionRepository;
use App\Service\WorldStateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/results', name: 'app_result_')]
final class ResultController extends AbstractController
{
    #[Route('/week/{weekNumber}', name: 'week_detail', methods: ['GET'])]
    public function weekDetail(
        int $weekNumber,
        WeeklyExecutionRepository $executionRepository,
    ): Response {
        $execution = $executionRepository->findByWeekNumber($weekNumber);

        if (!$execution) {
            throw $this->createNotFoundException(sprintf('Week %d execution not found', $weekNumber));
        }

        return $this->render('result/week_detail.html.twig', [
            'execution' => $execution,
            'week_number' => $weekNumber,
        ]);
    }

    #[Route('/week/{weekNumber}/json', name: 'week_json', methods: ['GET'])]
    public function weekJson(
        int $weekNumber,
        WeeklyExecutionRepository $executionRepository,
    ): \Symfony\Component\HttpFoundation\JsonResponse {
        $execution = $executionRepository->findByWeekNumber($weekNumber);

        if (!$execution) {
            return $this->json(['error' => 'Week execution not found'], 404);
        }

        return $this->json([
            'week_number' => $execution->getWeekNumber(),
            'execution_date' => $execution->getExecutionDate()->format('Y-m-d'),
            'status' => $execution->getStatus(),
            'execution_log' => $execution->getExecutionLog(),
            'network_effects' => $execution->getNetworkEffects(),
        ]);
    }

    #[Route('/latest', name: 'latest', methods: ['GET'])]
    public function latest(
        WeeklyExecutionRepository $executionRepository,
    ): Response {
        $execution = $executionRepository->findLatest();

        if (!$execution) {
            throw $this->createNotFoundException('No executions found');
        }

        return $this->redirectToRoute('app_result_week_detail', ['weekNumber' => $execution->getWeekNumber()]);
    }

    #[Route('/history', name: 'history', methods: ['GET'])]
    public function history(
        WeeklyExecutionRepository $executionRepository,
    ): Response {
        $executions = $executionRepository->findRecentExecutions(20);

        return $this->render('result/history.html.twig', [
            'executions' => $executions,
        ]);
    }

    #[Route('/world-events', name: 'world_events', methods: ['GET'])]
    public function worldEvents(
        WorldStateService $worldStateService,
    ): Response {
        $chronicle = $worldStateService->getChronicleLog(100);

        return $this->render('result/world_events.html.twig', [
            'events' => $chronicle,
        ]);
    }

    #[Route('/world-events/json', name: 'world_events_json', methods: ['GET'])]
    public function worldEventsJson(
        WorldStateService $worldStateService,
    ): \Symfony\Component\HttpFoundation\JsonResponse {
        $chronicle = $worldStateService->getChronicleLog(100);

        return $this->json(['events' => $chronicle]);
    }

    #[Route('/broadcasts', name: 'broadcasts', methods: ['GET'])]
    public function broadcasts(
        WorldStateService $worldStateService,
    ): Response {
        $broadcasts = $worldStateService->getNetworkBroadcast(null, 100);

        return $this->render('result/broadcasts.html.twig', [
            'broadcasts' => $broadcasts,
        ]);
    }

    #[Route('/broadcasts/json', name: 'broadcasts_json', methods: ['GET'])]
    public function broadcastsJson(
        WorldStateService $worldStateService,
    ): \Symfony\Component\HttpFoundation\JsonResponse {
        $broadcasts = $worldStateService->getNetworkBroadcast(null, 100);

        return $this->json(['broadcasts' => $broadcasts]);
    }
}
