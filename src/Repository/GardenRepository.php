<?php

namespace App\Repository;

use App\Entity\Garden;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Garden>
 */
class GardenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Garden::class);
    }

    /**
     * @return list<Garden>
     */
    public function findByOwnerId(int $ownerId): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('IDENTITY(g.owner) = :ownerId')
            ->setParameter('ownerId', $ownerId)
            ->orderBy('g.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
