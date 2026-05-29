<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\EventPost;
use App\Form\EventPostType;
use App\Repository\EventPostRepository;
use App\Entity\EventComment;
use App\Form\EventCommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
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

        if ($form->isSubmitted() && $this->isBotSubmission($form)) {
            return $this->redirectToRoute('basisvr_event_index');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', '投稿を公開しました。');
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('basisvr_event_index');
        }

        return $this->render('event/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'basisvr_event_show', methods: ['GET', 'POST'])]
    public function show(EventPost $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        $comment = new EventComment();
        $form = $this->createForm(EventCommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $this->isBotSubmission($form)) {
            return $this->redirectToRoute('basisvr_event_show', ['id' => $post->getId()]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setPost($post);
            $entityManager->persist($comment);
            $entityManager->flush();
            $this->addFlash('success', 'コメントを投稿しました。');

            return $this->redirectToRoute('basisvr_event_show', ['id' => $post->getId(), '_fragment' => 'comments']);
        }

        return $this->render('event/show.html.twig', [
            'post' => $post,
            'commentForm' => $form,
        ]);
    }

    /** @param FormInterface<mixed> $form */
    private function isBotSubmission(FormInterface $form): bool
    {
        if (! $form->has('website')) {
            return false;
        }

        return trim((string) $form->get('website')->getData()) !== '';
    }
}
