<?php

namespace App\Repository;

use App\Entity\WeeklyExecution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WeeklyExecution>
 */
class WeeklyExecutionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeeklyExecution::class);
    }

    public function findByWeekNumber(int $weekNumber): ?WeeklyExecution
    {
        return $this->createQueryBuilder('we')
            ->andWhere('we.weekNumber = :weekNumber')
            ->setParameter('weekNumber', $weekNumber)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLatest(): ?WeeklyExecution
    {
        return $this->createQueryBuilder('we')
            ->orderBy('we.weekNumber', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('we')
            ->andWhere('we.status = :status')
            ->setParameter('status', $status)
            ->orderBy('we.weekNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findRecentExecutions(int $limit = 10): array
    {
        return $this->createQueryBuilder('we')
            ->orderBy('we.weekNumber', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
