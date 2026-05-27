<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\EventPost;
use App\Form\EventPostType;
use App\Repository\EventPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/basisvr/events')]
class EventController extends AbstractController
{
    #[Route('', name: 'basisvr_event_index', methods: ['GET'])]
    public function index(EventPostRepository $eventPostRepository): Response
    {
        return $this->render('event/index.html.twig', [
            'posts' => $eventPostRepository->findLatest(),
        ]);
    }

    #[Route('/new', name: 'basisvr_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new EventPost();
        $form = $this->createForm(EventPostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('basisvr_event_index');
        }

        return $this->render('event/new.html.twig', [
            'form' => $form,
        ]);
    }
}
