<?php

namespace App\Repository;

use App\Entity\Challenge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Challenge>
 */
class ChallengeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Challenge::class);
    }

    public function findActive(): ?Challenge
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.active = true')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByPeriod(string $period): ?Challenge
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.period = :period')
            ->setParameter('period', $period)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
