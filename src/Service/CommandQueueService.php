<?php

namespace App\Service;

use App\Entity\CommandQueue;
use App\Entity\Garden;
use App\Entity\Tile;
use App\Entity\User;
use App\Repository\CommandQueueRepository;
use Doctrine\ORM\EntityManagerInterface;

final class CommandQueueService
{
    public function __construct(
        private readonly CommandQueueRepository $commandQueueRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function addCommand(
        User $user,
        Garden $garden,
        array $command,
        ?Tile $tile = null,
        ?\DateTimeImmutable $date = null,
    ): CommandQueue {
        $date ??= new \DateTimeImmutable();

        // Check 1 command per day rule
        $existingCommands = $this->commandQueueRepository->findPendingByUser($user->getId() ?? 0, $date);
        if (!empty($existingCommands)) {
            throw new \InvalidArgumentException('You can only add one command per day');
        }

        $cq = new CommandQueue();
        $cq->setUser($user);
        $cq->setGarden($garden);
        $cq->setTile($tile);
        $cq->setCommand($command);
        $cq->setInsertedDate($date);
        $cq->setStatus('pending');

        $this->em->persist($cq);
        $this->em->flush();

        return $cq;
    }

    public function queueCommandsForExecution(int $week): void
    {
        // Fetch all pending commands and mark them as queued
        $pendingCommands = $this->em->createQueryBuilder()
            ->select('cq')
            ->from(CommandQueue::class, 'cq')
            ->andWhere('cq.status = :status')
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getResult();

        foreach ($pendingCommands as $cmd) {
            $cmd->setExecutionWeek($week);
            $cmd->setStatus('queued');
        }

        $this->em->flush();
    }

    public function getCommandsByGardenAndWeek(int $gardenId, int $week): array
    {
        return $this->commandQueueRepository->findByGardenAndWeek($gardenId, $week);
    }

    public function getQueuedCommandsByWeek(int $week): array
    {
        return $this->commandQueueRepository->findQueuedByWeek($week);
    }

    public function markCommandExecuted(CommandQueue $command, array $executionResult): void
    {
        $command->setStatus('executed');
        $command->setExecutionResult($executionResult);
        $command->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();
    }

    public function markCommandFailed(CommandQueue $command, string $errorMessage): void
    {
        $command->setStatus('failed');
        $command->setExecutionResult(['error' => $errorMessage]);
        $command->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();
    }

    public function deleteCommand(CommandQueue $command): void
    {
        if ($command->getStatus() !== 'pending') {
            throw new \InvalidArgumentException('Can only delete pending commands');
        }

        $this->em->remove($command);
        $this->em->flush();
    }

    public function getUserCommands(int $userId, int $gardenId): array
    {
        return $this->commandQueueRepository->findByUserAndGarden($userId, $gardenId);
    }

    public function getCommandHistory(int $gardenId, int $limit = 50): array
    {
        return $this->em->createQueryBuilder()
            ->select('cq')
            ->from(CommandQueue::class, 'cq')
            ->andWhere('cq.garden = :gardenId')
            ->andWhere('cq.status IN (:statuses)')
            ->setParameter('gardenId', $gardenId)
            ->setParameter('statuses', ['executed', 'failed'])
            ->orderBy('cq.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
