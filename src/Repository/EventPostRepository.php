<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\EventPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventPost>
 */
class EventPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventPost::class);
    }

    /** @return EventPost[] */
    public function findLatest(int $limit = 20): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
