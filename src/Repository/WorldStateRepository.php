<?php

namespace App\Repository;

use App\Entity\WorldState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorldState>
 */
class WorldStateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorldState::class);
    }

    public function getOrCreate(): WorldState
    {
        $worldState = $this->find(1);

        if (!$worldState) {
            $worldState = new WorldState();
            $this->getEntityManager()->persist($worldState);
            $this->getEntityManager()->flush();
        }

        return $worldState;
    }
}
