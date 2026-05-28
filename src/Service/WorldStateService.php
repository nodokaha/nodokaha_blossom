<?php

namespace App\Service;

use App\Entity\WorldState;
use App\Repository\WorldStateRepository;
use Doctrine\ORM\EntityManagerInterface;

final class WorldStateService
{
    public function __construct(
        private readonly WorldStateRepository $worldStateRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function getWorldState(): WorldState
    {
        return $this->worldStateRepository->getOrCreate();
    }

    public function updateCurrentDay(int $day): void
    {
        $world = $this->getWorldState();
        $world->setCurrentDay($day);
        $world->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();
    }

    public function updateCurrentWeek(int $week): void
    {
        $world = $this->getWorldState();
        $world->setCurrentWeek($week);
        $world->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();
    }

    public function setChapter(string $title, string $objective): void
    {
        $world = $this->getWorldState();
        $world->setChapter($title);
        $world->setObjective($objective);
        $world->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();
    }

    public function updateBiomeData(array $biomeData): void
    {
        $world = $this->getWorldState();
        $current = $world->getBiomeData();
        $world->setBiomeData(array_merge($current, $biomeData));
        $world->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();
    }

    public function getBiomeData(): array
    {
        return $this->getWorldState()->getBiomeData();
    }

    public function addToGlobalStack(array $stackItem): void
    {
        $world = $this->getWorldState();
        $stack = $world->getGlobalStack();
        $stack[] = $stackItem;
        $world->setGlobalStack($stack);
        $world->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();
    }

    public function getGlobalStack(): array
    {
        return $this->getWorldState()->getGlobalStack();
    }

    public function clearGlobalStack(): void
    {
        $world = $this->getWorldState();
        $world->setGlobalStack([]);
        $world->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();
    }

    public function addChronicleEntry(string $message, array $metadata = []): void
    {
        $world = $this->getWorldState();
        $chronicle = $world->getChronicleLog();
        $chronicle[] = [
            'timestamp' => (new \DateTimeImmutable())->format(\DateTimeImmutable::ISO8601),
            'message' => $message,
            'metadata' => $metadata,
        ];
        $world->setChronicleLog($chronicle);
        $world->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();
    }

    public function getChronicleLog(int $limit = 100): array
    {
        $chronicle = $this->getWorldState()->getChronicleLog();

        return array_slice($chronicle, -$limit);
    }

    public function addBroadcast(string $channel, array $message): void
    {
        $world = $this->getWorldState();
        $broadcasts = $world->getNetworkBroadcast();
        $broadcasts[] = [
            'channel' => $channel,
            'timestamp' => (new \DateTimeImmutable())->format(\DateTimeImmutable::ISO8601),
            'message' => $message,
        ];
        $world->setNetworkBroadcast(array_slice($broadcasts, -100));
        $world->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();
    }

    public function getNetworkBroadcast(?string $channel = null, int $limit = 50): array
    {
        $broadcasts = $this->getWorldState()->getNetworkBroadcast();

        if ($channel) {
            $broadcasts = array_filter($broadcasts, fn($b) => $b['channel'] === $channel);
        }

        return array_slice($broadcasts, -$limit);
    }

    public function getState(): array
    {
        $world = $this->getWorldState();

        return [
            'calendar_start' => $world->getCalendarStart()->format('Y-m-d'),
            'current_day' => $world->getCurrentDay(),
            'current_week' => $world->getCurrentWeek(),
            'chapter' => $world->getChapter(),
            'objective' => $world->getObjective(),
            'biome' => $world->getBiomeData(),
            'global_stack' => $world->getGlobalStack(),
            'chronicle' => $world->getChronicleLog(),
            'network_broadcast' => $world->getNetworkBroadcast(),
        ];
    }
}
