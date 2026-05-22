<?php

namespace App\Controller;

use App\Repository\GardenRepository;
use App\Entity\User;
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

        foreach ($gardens as $garden) {
            $ownerKey = strtolower($garden->getOwner()?->getEmail() ?? '');
            $targeted = (float) ($network['garden_influence'][$ownerKey] ?? 0);
            $shared = (float) ($network['garden_influence']['all'] ?? 0);
            $statuses[$garden->getId() ?? spl_object_id($garden)] = $gardenBalanceService->calculateStatus($garden, $targeted + $shared);
        }

        return $this->render('garden/list.html.twig', [
            'gardens' => $gardens,
            'network' => $network,
            'statuses' => $statuses,
        ]);
    }

    #[Route('/my-garden/{userId}', name: 'app_garden_dashboard', methods: ['GET'])]
    public function dashboard(int $userId, GardenRepository $gardenRepository): Response
    {
        $currentUser = $this->getUser();

        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException('ログインが必要です。');
        }

        if ($currentUser->getId() !== $userId) {
            throw $this->createAccessDeniedException('この箱庭にはアクセスできません。');
        }

        return $this->render('garden/dashboard.html.twig', [
            'owner' => $currentUser,
            'gardens' => $gardenRepository->findByOwnerId($userId),
        ]);
    }
}
