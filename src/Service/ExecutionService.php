<?php

namespace App\Service;

use App\Entity\WeeklyExecution;
use App\Repository\CommandQueueRepository;
use App\Repository\WeeklyExecutionRepository;
use Doctrine\ORM\EntityManagerInterface;

final class ExecutionService
{
    public function __construct(
        private readonly CommandQueueRepository $commandQueueRepository,
        private readonly WeeklyExecutionRepository $executionRepository,
        private readonly CommandQueueService $commandQueueService,
        private readonly StoryVmService $storyVmService,
        private readonly WorldStateService $worldStateService,
        private readonly InfluenceService $influenceService,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function executeWeeklyCommands(int $weekNumber): WeeklyExecution
    {
        // Get all queued commands for the week
        $queuedCommands = $this->commandQueueRepository->findQueuedByWeek($weekNumber);

        $executionLog = [];
        $networkSignals = [];
        $globalStack = $this->worldStateService->getGlobalStack();

        // Group commands by garden
        $commandsByGarden = [];
        foreach ($queuedCommands as $cmd) {
            $gardenId = $cmd->getGarden()->getId() ?? 0;
            if (!isset($commandsByGarden[$gardenId])) {
                $commandsByGarden[$gardenId] = [];
            }
            $commandsByGarden[$gardenId][] = $cmd;
        }

        // Execute commands for each garden
        foreach ($commandsByGarden as $gardenId => $commands) {
            foreach ($commands as $cmd) {
                try {
                    $stackData = $cmd->getCommand() ? [$cmd->getCommand()] : [];
                    $result = $this->storyVmService->runTileProgram($stackData, $globalStack);

                    // Process network signals
                    if (isset($result['network_signals'])) {
                        foreach ($result['network_signals'] as $signal) {
                            $networkSignals[] = $signal;
                            if ($signal['type'] === 'broadcast') {
                                $this->influenceService->processBroadcast(
                                    $signal['channel'],
                                    $signal['impact'],
                                    $cmd->getUser()
                                );
                            } elseif ($signal['type'] === 'influence') {
                                $this->influenceService->processInfluence(
                                    $signal['target'],
                                    $signal['impact'],
                                    $cmd->getUser()
                                );
                            }
                        }
                    }

                    // Mark command as executed
                    $this->commandQueueService->markCommandExecuted($cmd, $result);

                    $executionLog[] = [
                        'garden_id' => $gardenId,
                        'command' => $cmd->getCommand(),
                        'result' => $result,
                        'status' => 'success',
                    ];
                } catch (\Exception $e) {
                    $this->commandQueueService->markCommandFailed($cmd, $e->getMessage());
                    $executionLog[] = [
                        'garden_id' => $gardenId,
                        'command' => $cmd->getCommand(),
                        'error' => $e->getMessage(),
                        'status' => 'failed',
                    ];
                }
            }
        }

        // Create WeeklyExecution record
        $execution = new WeeklyExecution();
        $execution->setWeekNumber($weekNumber);
        $execution->setExecutionDate(new \DateTimeImmutable());
        $execution->setStatus('completed');
        $execution->setExecutionLog($executionLog);
        $execution->setNetworkEffects($networkSignals);

        $this->em->persist($execution);
        $this->em->flush();

        // Advance to next week and add chronicle entry
        $worldState = $this->worldStateService->getWorldState();
        $worldState->setCurrentWeek($weekNumber + 1);
        $worldState->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();

        $this->worldStateService->addChronicleEntry(
            sprintf('Week %d execution completed with %d commands', $weekNumber, count($queuedCommands)),
            ['week' => $weekNumber, 'command_count' => count($queuedCommands)]
        );

        return $execution;
    }

    public function queueCommandsForNextExecution(int $week): void
    {
        $this->commandQueueService->queueCommandsForExecution($week);
    }
}
