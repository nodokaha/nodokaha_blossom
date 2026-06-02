<?php

namespace App\Repository;

use App\Entity\Asset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AssetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Asset::class);
    }

    /**
     * @return Asset[]
     */
    public function findByTypeOrdered(string $type): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.type = :type')
            ->setParameter('type', $type)
            ->orderBy('a.uploadedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Asset[]
     */
    public function search(string $term): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('LOWER(a.name) LIKE :term OR LOWER(a.description) LIKE :term')
            ->setParameter('term', '%'.strtolower($term).'%')
            ->orderBy('a.uploadedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
