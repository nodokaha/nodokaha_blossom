<?php

namespace App\Controller;

use App\Repository\GardenRepository;
use App\Service\GardenBalanceService;
use App\Service\WorldStateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/dashboard', name: 'app_admin_dashboard_')]
final class AdminDashboardController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        GardenRepository $gardenRepository,
        GardenBalanceService $gardenBalanceService,
        WorldStateService $worldStateService,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $gardens = $gardenRepository->findAll();
        $worldState = $worldStateService->getWorldState();

        $gardenStatuses = [];
        foreach ($gardens as $garden) {
            $gardenStatuses[$garden->getId() ?? spl_object_id($garden)] = $gardenBalanceService->calculateStatus($garden);
        }

        return $this->render('admin/dashboard.html.twig', [
            'gardens' => $gardens,
            'statuses' => $gardenStatuses,
            'world_state' => $worldState,
        ]);
    }

    #[Route('/world', name: 'world', methods: ['GET'])]
    public function worldState(
        WorldStateService $worldStateService,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $state = $worldStateService->getState();

        return $this->render('admin/world_state.html.twig', [
            'state' => $state,
        ]);
    }

    #[Route('/world/json', name: 'world_json', methods: ['GET'])]
    public function worldStateJson(
        WorldStateService $worldStateService,
    ): \Symfony\Component\HttpFoundation\JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $state = $worldStateService->getState();

        return $this->json($state);
    }
}
