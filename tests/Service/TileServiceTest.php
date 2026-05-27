<?php

namespace App\Tests\Service;

use App\Entity\Garden;
use App\Entity\Tile;
use App\Entity\User;
use App\Repository\TileRepository;
use App\Service\TileService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class TileServiceTest extends TestCase
{
    private TileService $tileService;
    private TileRepository $tileRepository;
    private EntityManagerInterface $em;
    private Garden $garden;

    protected function setUp(): void
    {
        $this->tileRepository = $this->createMock(TileRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->tileService = new TileService($this->tileRepository, $this->em);

        $user = new User();
        $user->setEmail('test@example.com');
        $this->garden = new Garden();
        $this->garden->setOwner($user);
        $this->garden->setName('Test Garden');
    }

    public function testCreateTile(): void
    {
        $this->tileRepository->expects($this->once())
            ->method('findByCoordinates')
            ->willReturn(null);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $tile = $this->tileService->createTile($this->garden, 0, 0, 'field');

        $this->assertInstanceOf(Tile::class, $tile);
        $this->assertEquals(0, $tile->getX());
        $this->assertEquals(0, $tile->getY());
        $this->assertEquals('field', $tile->getRole());
    }

    public function testCreateTileThrowsExceptionIfExists(): void
    {
        $existingTile = new Tile();
        $this->tileRepository->expects($this->once())
            ->method('findByCoordinates')
            ->willReturn($existingTile);

        $this->expectException(\InvalidArgumentException::class);
        $this->tileService->createTile($this->garden, 0, 0, 'field');
    }

    public function testGetTile(): void
    {
        $tile = new Tile();
        $this->tileRepository->expects($this->once())
            ->method('findByCoordinates')
            ->with(1, 0, 0)
            ->willReturn($tile);

        $result = $this->tileService->getTile(1, 0, 0);
        $this->assertSame($tile, $result);
    }

    public function testInitializeGardenField(): void
    {
        $this->tileRepository->expects($this->never())->method('findByCoordinates');
        $this->em->expects($this->atLeast(96))->method('persist'); // 12 * 8 = 96 tiles
        $this->em->expects($this->once())->method('flush');

        $tiles = $this->tileService->initializeGardenField($this->garden, 12, 8);

        $this->assertCount(96, $tiles);
        foreach ($tiles as $tile) {
            $this->assertInstanceOf(Tile::class, $tile);
            $this->assertEquals('field', $tile->getRole());
        }
    }
}
