<?php

namespace App\Tests\Service;

use App\Entity\WorldState;
use App\Repository\WorldStateRepository;
use App\Service\WorldStateService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class WorldStateServiceTest extends TestCase
{
    private WorldStateService $worldStateService;
    private WorldStateRepository $worldStateRepository;
    private EntityManagerInterface $em;
    private WorldState $worldState;

    protected function setUp(): void
    {
        $this->worldStateRepository = $this->createMock(WorldStateRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->worldStateService = new WorldStateService($this->worldStateRepository, $this->em);

        $this->worldState = new WorldState();
        $this->worldStateRepository->expects($this->any())
            ->method('getOrCreate')
            ->willReturn($this->worldState);
    }

    public function testUpdateCurrentDay(): void
    {
        $this->em->expects($this->once())->method('flush');

        $this->worldStateService->updateCurrentDay(5);

        $this->assertEquals(5, $this->worldState->getCurrentDay());
    }

    public function testUpdateCurrentWeek(): void
    {
        $this->em->expects($this->once())->method('flush');

        $this->worldStateService->updateCurrentWeek(3);

        $this->assertEquals(3, $this->worldState->getCurrentWeek());
    }

    public function testSetChapter(): void
    {
        $this->em->expects($this->once())->method('flush');

        $this->worldStateService->setChapter('Chapter 2', 'New objective');

        $this->assertEquals('Chapter 2', $this->worldState->getChapter());
        $this->assertEquals('New objective', $this->worldState->getObjective());
    }

    public function testAddToGlobalStack(): void
    {
        $this->em->expects($this->once())->method('flush');

        $item = ['type' => 'broadcast', 'channel' => 'global', 'impact' => 1];
        $this->worldStateService->addToGlobalStack($item);

        $stack = $this->worldState->getGlobalStack();
        $this->assertContains($item, $stack);
    }

    public function testGetGlobalStack(): void
    {
        $this->worldState->setGlobalStack([
            ['type' => 'broadcast', 'channel' => 'global', 'impact' => 1],
        ]);

        $stack = $this->worldStateService->getGlobalStack();

        $this->assertCount(1, $stack);
        $this->assertEquals('broadcast', $stack[0]['type']);
    }

    public function testAddChronicleEntry(): void
    {
        $this->em->expects($this->once())->method('flush');

        $this->worldStateService->addChronicleEntry('Test event', ['metadata' => 'value']);

        $chronicle = $this->worldState->getChronicleLog();
        $this->assertNotEmpty($chronicle);
        $this->assertEquals('Test event', $chronicle[0]['message']);
    }
}
