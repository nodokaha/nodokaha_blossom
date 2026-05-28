<?php

namespace App\Repository;

use App\Entity\CommandQueue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommandQueue>
 */
class CommandQueueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandQueue::class);
    }

    public function findPendingByUser(int $userId, \DateTimeImmutable $date): array
    {
        return $this->createQueryBuilder('cq')
            ->andWhere('cq.user = :userId')
            ->andWhere('cq.insertedDate = :date')
            ->andWhere('cq.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('date', $date)
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getResult();
    }

    public function findByGardenAndWeek(int $gardenId, int $week): array
    {
        return $this->createQueryBuilder('cq')
            ->andWhere('cq.garden = :gardenId')
            ->andWhere('cq.executionWeek = :week')
            ->andWhere('cq.status IN (:statuses)')
            ->setParameter('gardenId', $gardenId)
            ->setParameter('week', $week)
            ->setParameter('statuses', ['pending', 'queued'])
            ->orderBy('cq.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findQueuedByWeek(int $week): array
    {
        return $this->createQueryBuilder('cq')
            ->andWhere('cq.executionWeek = :week')
            ->andWhere('cq.status = :status')
            ->setParameter('week', $week)
            ->setParameter('status', 'queued')
            ->orderBy('cq.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByUserAndGarden(int $userId, int $gardenId): array
    {
        return $this->createQueryBuilder('cq')
            ->andWhere('cq.user = :userId')
            ->andWhere('cq.garden = :gardenId')
            ->setParameter('userId', $userId)
            ->setParameter('gardenId', $gardenId)
            ->orderBy('cq.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
