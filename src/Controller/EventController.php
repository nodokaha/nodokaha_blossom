<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/events', name: 'event_')]
class EventController extends AbstractController
{
    private EventRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(EventRepository $repository, EntityManagerInterface $entityManager)
    {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $events = $this->repository->findUpcoming(50);

        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/calendar', name: 'calendar', methods: ['GET'])]
    public function calendar(Request $request): Response
    {
        $month = max(1, min(12, (int) $request->query->get('month', (int) date('m'))));
        $year = max(1970, (int) $request->query->get('year', (int) date('Y')));

        $start = new \DateTimeImmutable(sprintf('%04d-%02d-01 00:00:00', $year, $month));
        $events = $this->repository->findForMonth($year, $month);
        $calendar = $this->buildCalendar($start, $events);

        return $this->render('event/calendar.html.twig', [
            'calendar' => $calendar,
            'month' => $month,
            'year' => $year,
            'events' => $events,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($event);
            $this->entityManager->flush();

            $this->addFlash('success', 'イベントを作成しました。');

            return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
        }

        return $this->render('event/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            $this->addFlash('success', 'イベントを更新しました。');

            return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
        }

        return $this->render('event/form.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Event $event): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
        }

        $this->entityManager->remove($event);
        $this->entityManager->flush();

        $this->addFlash('success', 'イベントを削除しました。');

        return $this->redirectToRoute('event_index');
    }

    /**
     * @param Event[] $events
     * @return array<int, array<int, array{date:\DateTimeImmutable, events: Event[]}|null>>
     */
    private function buildCalendar(\DateTimeImmutable $monthStart, array $events): array
    {
        $daysInMonth = (int) $monthStart->format('t');
        $firstDayWeek = (int) $monthStart->format('N');
        $weeks = [];
        $currentDay = 1;

        while ($currentDay <= $daysInMonth) {
            $week = [];

            for ($weekday = 1; $weekday <= 7; $weekday++) {
                if (count($weeks) === 0 && $weekday < $firstDayWeek) {
                    $week[] = null;
                    continue;
                }

                if ($currentDay > $daysInMonth) {
                    $week[] = null;
                    continue;
                }

                $date = $monthStart->modify(sprintf('+%d days', $currentDay - 1));
                $dayEvents = [];

                foreach ($events as $event) {
                    $eventStart = $event->getStartAt();
                    $eventEnd = $event->getEndAt() ?? $eventStart;
                    if ($eventStart <= $date && $eventEnd >= $date) {
                        $dayEvents[] = $event;
                    }
                }

                $week[] = [
                    'date' => $date,
                    'events' => $dayEvents,
                ];

                $currentDay++;
            }

            $weeks[] = $week;
        }

        return $weeks;
    }
}
