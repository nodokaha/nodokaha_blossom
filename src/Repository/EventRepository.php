<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @return Event[]
     */
    public function findUpcoming(int $limit = 20): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.startAt >= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('e.startAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Event[]
     */
    public function findForMonth(int $year, int $month): array
    {
        $start = new \DateTimeImmutable(sprintf('%04d-%02d-01 00:00:00', $year, $month));
        $end = $start->modify('first day of next month');

        return $this->createQueryBuilder('e')
            ->andWhere('e.startAt < :end')
            ->andWhere('(e.endAt >= :start OR (e.endAt IS NULL AND e.startAt >= :start))')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('e.startAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
