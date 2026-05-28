<?php

namespace App\Entity;

use App\Repository\WorldStateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorldStateRepository::class)]
class WorldState
{
    #[ORM\Id]
    #[ORM\Column]
    private int $id = 1; // singleton

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $calendarStart;

    #[ORM\Column]
    private int $currentDay = 1;

    #[ORM\Column]
    private int $currentWeek = 1;

    #[ORM\Column(length: 255)]
    private string $chapter = 'CHAPTER 1: 花畑の起動';

    #[ORM\Column(type: Types::TEXT)]
    private string $objective = '光と水の変数を整えて、開花率を20%以上にする';

    #[ORM\Column(type: Types::JSON)]
    private array $biomeData = [
        'weather' => 'mist',
        'bloom_rate' => 0,
        'energy' => 5,
    ];

    #[ORM\Column(type: Types::JSON)]
    private array $globalStack = [];

    #[ORM\Column(type: Types::JSON)]
    private array $chronicleLog = [];

    #[ORM\Column(type: Types::JSON)]
    private array $networkBroadcast = [];

    #[ORM\Column]
    private int $version = 0; // for optimistic locking

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->id = 1;
        $this->calendarStart = new \DateTimeImmutable('2026-01-01');
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCalendarStart(): \DateTimeImmutable
    {
        return $this->calendarStart;
    }

    public function setCalendarStart(\DateTimeImmutable $calendarStart): static
    {
        $this->calendarStart = $calendarStart;

        return $this;
    }

    public function getCurrentDay(): int
    {
        return $this->currentDay;
    }

    public function setCurrentDay(int $currentDay): static
    {
        $this->currentDay = $currentDay;

        return $this;
    }

    public function getCurrentWeek(): int
    {
        return $this->currentWeek;
    }

    public function setCurrentWeek(int $currentWeek): static
    {
        $this->currentWeek = $currentWeek;

        return $this;
    }

    public function getChapter(): string
    {
        return $this->chapter;
    }

    public function setChapter(string $chapter): static
    {
        $this->chapter = $chapter;

        return $this;
    }

    public function getObjective(): string
    {
        return $this->objective;
    }

    public function setObjective(string $objective): static
    {
        $this->objective = $objective;

        return $this;
    }

    public function getBiomeData(): array
    {
        return $this->biomeData;
    }

    public function setBiomeData(array $biomeData): static
    {
        $this->biomeData = $biomeData;

        return $this;
    }

    public function getGlobalStack(): array
    {
        return $this->globalStack;
    }

    public function setGlobalStack(array $globalStack): static
    {
        $this->globalStack = $globalStack;

        return $this;
    }

    public function getChronicleLog(): array
    {
        return $this->chronicleLog;
    }

    public function setChronicleLog(array $chronicleLog): static
    {
        $this->chronicleLog = $chronicleLog;

        return $this;
    }

    public function getNetworkBroadcast(): array
    {
        return $this->networkBroadcast;
    }

    public function setNetworkBroadcast(array $networkBroadcast): static
    {
        $this->networkBroadcast = $networkBroadcast;

        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
