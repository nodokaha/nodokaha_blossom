<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\GardenRepository;
use App\Service\CommandQueueService;
use App\Service\GardenBalanceService;
use App\Service\StoryVmStateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GardenController extends AbstractController
{
    #[Route('/gardens', name: 'app_garden_list', methods: ['GET'])]
    public function list(GardenRepository $gardenRepository, StoryVmStateService $storyVmStateService, GardenBalanceService $gardenBalanceService): Response
    {
        $state = $storyVmStateService->loadState();
        $network = is_array($state['world']['network'] ?? null) ? $state['world']['network'] : [];

        $gardens = $gardenRepository->findBy([], ['id' => 'ASC']);
        $statuses = [];
        $rankedGardens = [];

        foreach ($gardens as $garden) {
            $ownerKey = strtolower($garden->getOwner()?->getEmail() ?? '');
            $targeted = (float) ($network['garden_influence'][$ownerKey] ?? 0);
            $shared = (float) ($network['garden_influence']['all'] ?? 0);
            $statuses[$garden->getId() ?? spl_object_id($garden)] = $gardenBalanceService->calculateStatus($garden, $targeted + $shared);
            $rankedGardens[] = [
                'garden' => $garden,
                'status' => $statuses[$garden->getId() ?? spl_object_id($garden)],
                'score' => $statuses[$garden->getId() ?? spl_object_id($garden)]['population'] + $statuses[$garden->getId() ?? spl_object_id($garden)]['treasury'] + $statuses[$garden->getId() ?? spl_object_id($garden)]['food'],
            ];
        }

        usort($rankedGardens, fn(array $a, array $b): int => $b['score'] <=> $a['score']);

        return $this->render('garden/list.html.twig', [
            'gardens' => $gardens,
            'network' => $network,
            'statuses' => $statuses,
            'rankedGardens' => $rankedGardens,
        ]);
    }

    #[Route('/my-garden/{userId}', name: 'app_garden_dashboard', methods: ['GET'])]
    public function dashboard(int $userId, GardenRepository $gardenRepository, StoryVmStateService $storyVmStateService, GardenBalanceService $gardenBalanceService, CommandQueueService $commandQueueService): Response
    {
        $currentUser = $this->getUser();
        $isOwner = $currentUser instanceof User && $currentUser->getId() === $userId;

        $state = $storyVmStateService->loadState();
        $network = is_array($state['world']['network'] ?? null) ? $state['world']['network'] : [];

        $gardens = $gardenRepository->findByOwnerId($userId);
        $primaryGarden = $gardens[0] ?? null;
        if (!$primaryGarden) {
            throw $this->createNotFoundException('この箱庭は存在しません。');
        }

        $ownerKey = strtolower($primaryGarden->getOwner()?->getEmail() ?? '');
        $targeted = (float) ($network['garden_influence'][$ownerKey] ?? 0);
        $shared = (float) ($network['garden_influence']['all'] ?? 0);

        $status = $gardenBalanceService->calculateStatus($primaryGarden, $targeted + $shared);
        $commandHistory = $commandQueueService->getCommandHistory($primaryGarden->getId() ?? 0, 20);

        return $this->render('garden/dashboard.html.twig', [
            'owner' => $primaryGarden->getOwner(),
            'is_owner' => $isOwner,
            'gardens' => $gardens,
            'state' => $state,
            'status' => $status,
            'network_targeted' => $targeted,
            'network_shared' => $shared,
            'command_history' => $commandHistory,
        ]);
    }
}
