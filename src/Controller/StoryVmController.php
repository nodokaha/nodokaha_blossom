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

        return $this->render('story_vm/index.html.twig', [
            'daily_token_limit' => $dailyTokenLimit,
        ]);
    }
}
