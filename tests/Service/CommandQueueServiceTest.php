<?php

namespace App\Tests\Service;

use App\Entity\CommandQueue;
use App\Entity\Garden;
use App\Entity\User;
use App\Repository\CommandQueueRepository;
use App\Service\CommandQueueService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CommandQueueServiceTest extends TestCase
{
    private CommandQueueService $commandQueueService;
    private CommandQueueRepository $commandQueueRepository;
    private EntityManagerInterface $em;
    private User $user;
    private Garden $garden;

    protected function setUp(): void
    {
        $this->commandQueueRepository = $this->createMock(CommandQueueRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->commandQueueService = new CommandQueueService($this->commandQueueRepository, $this->em);

        $this->user = new User();
        $this->user->setEmail('test@example.com');

        $this->garden = new Garden();
        $this->garden->setOwner($this->user);
        $this->garden->setName('Test Garden');
    }

    public function testAddCommand(): void
    {
        $this->commandQueueRepository->expects($this->once())
            ->method('findPendingByUser')
            ->willReturn([]);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $command = ['opcode' => 'LDC', 'args' => '5'];
        $cq = $this->commandQueueService->addCommand($this->user, $this->garden, $command);

        $this->assertInstanceOf(CommandQueue::class, $cq);
        $this->assertEquals('pending', $cq->getStatus());
        $this->assertEquals($command, $cq->getCommand());
    }

    public function testAddCommandThrowsExceptionIfAlreadyExists(): void
    {
        $existingCommand = new CommandQueue();
        $this->commandQueueRepository->expects($this->once())
            ->method('findPendingByUser')
            ->willReturn([$existingCommand]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('You can only add one command per day');

        $command = ['opcode' => 'LDC', 'args' => '5'];
        $this->commandQueueService->addCommand($this->user, $this->garden, $command);
    }

    public function testDeleteCommand(): void
    {
        $cq = new CommandQueue();
        $cq->setStatus('pending');

        $this->em->expects($this->once())->method('remove');
        $this->em->expects($this->once())->method('flush');

        $this->commandQueueService->deleteCommand($cq);
    }

    public function testDeleteCommandThrowsExceptionIfNotPending(): void
    {
        $cq = new CommandQueue();
        $cq->setStatus('executed');

        $this->expectException(\InvalidArgumentException::class);
        $this->commandQueueService->deleteCommand($cq);
    }

    public function testMarkCommandExecuted(): void
    {
        $cq = new CommandQueue();
        $result = ['stack' => [5, 3, 8], 'env' => []];

        $this->em->expects($this->once())->method('flush');

        $this->commandQueueService->markCommandExecuted($cq, $result);

        $this->assertEquals('executed', $cq->getStatus());
        $this->assertEquals($result, $cq->getExecutionResult());
    }
}
