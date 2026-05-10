<?php

namespace App\Controller;

use App\Repository\GardenRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GardenController extends AbstractController
{
    #[Route('/gardens', name: 'app_garden_list', methods: ['GET'])]
    public function list(GardenRepository $gardenRepository): Response
    {
        return $this->render('garden/list.html.twig', [
            'gardens' => $gardenRepository->findBy([], ['id' => 'ASC']),
        ]);
    }

    #[Route('/my-garden/{userId}', name: 'app_garden_dashboard', methods: ['GET'])]
    public function dashboard(int $userId, UserRepository $userRepository, GardenRepository $gardenRepository): Response
    {
        $user = $userRepository->find($userId);

        if ($user === null) {
            throw $this->createNotFoundException('ユーザーが見つかりません。');
        }

        return $this->render('garden/dashboard.html.twig', [
            'owner' => $user,
            'gardens' => $gardenRepository->findByOwnerId($userId),
        ]);
    }
}
