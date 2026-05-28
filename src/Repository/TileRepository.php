<?php

namespace App\Repository;

use App\Entity\Tile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tile>
 */
class TileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tile::class);
    }

    public function findByCoordinates(int $gardenId, int $x, int $y): ?Tile
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.garden = :gardenId')
            ->andWhere('t.x = :x')
            ->andWhere('t.y = :y')
            ->setParameter('gardenId', $gardenId)
            ->setParameter('x', $x)
            ->setParameter('y', $y)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByGarden(int $gardenId): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.garden = :gardenId')
            ->setParameter('gardenId', $gardenId)
            ->orderBy('t.y', 'ASC')
            ->addOrderBy('t.x', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByRole(int $gardenId, string $role): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.garden = :gardenId')
            ->andWhere('t.role = :role')
            ->setParameter('gardenId', $gardenId)
            ->setParameter('role', $role)
            ->getQuery()
            ->getResult();
    }
}
