<?php

namespace App\Service;

use App\Entity\Garden;
use App\Entity\Tile;
use App\Repository\TileRepository;
use Doctrine\ORM\EntityManagerInterface;

final class TileService
{
    public function __construct(
        private readonly TileRepository $tileRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function createTile(Garden $garden, int $x, int $y, string $role = 'field'): Tile
    {
        if ($this->tileRepository->findByCoordinates($garden->getId() ?? 0, $x, $y)) {
            throw new \InvalidArgumentException(sprintf('Tile at (%d, %d) already exists', $x, $y));
        }

        $tile = new Tile();
        $tile->setGarden($garden);
        $tile->setX($x);
        $tile->setY($y);
        $tile->setRole($role);
        $tile->setStackData([]);
        $tile->setStackState([]);

        $this->em->persist($tile);
        $this->em->flush();

        return $tile;
    }

    public function getTile(int $gardenId, int $x, int $y): ?Tile
    {
        return $this->tileRepository->findByCoordinates($gardenId, $x, $y);
    }

    public function getTilesByGarden(int $gardenId): array
    {
        return $this->tileRepository->findByGarden($gardenId);
    }

    public function getTilesByRole(int $gardenId, string $role): array
    {
        return $this->tileRepository->findByRole($gardenId, $role);
    }

    public function updateTileStack(Tile $tile, array $stackData): void
    {
        $tile->setStackData($stackData);
        $tile->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();
    }

    public function updateTileStackState(Tile $tile, array $stackState): void
    {
        $tile->setStackState($stackState);
        $tile->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();
    }

    public function deleteTile(Tile $tile): void
    {
        $this->em->remove($tile);
        $this->em->flush();
    }

    public function initializeGardenField(Garden $garden, int $width = 12, int $height = 8): array
    {
        $tiles = [];
        for ($y = 0; $y < $height; ++$y) {
            for ($x = 0; $x < $width; ++$x) {
                $tiles[] = $this->createTile($garden, $x, $y, 'field');
            }
        }

        return $tiles;
    }

    public function getGardenDimensions(int $gardenId): array
    {
        $tiles = $this->getTilesByGarden($gardenId);
        if (empty($tiles)) {
            return ['width' => 0, 'height' => 0];
        }

        $maxX = max(array_map(fn(Tile $t) => $t->getX(), $tiles));
        $maxY = max(array_map(fn(Tile $t) => $t->getY(), $tiles));

        return [
            'width' => $maxX + 1,
            'height' => $maxY + 1,
        ];
    }
}
