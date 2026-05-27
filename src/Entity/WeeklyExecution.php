<?php

namespace App\Entity;

use App\Repository\WeeklyExecutionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WeeklyExecutionRepository::class)]
class WeeklyExecution
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private int $weekNumber = 0;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $executionDate;

    #[ORM\Column(length: 50)]
    private string $status = 'pending'; // pending, executing, completed, failed

    #[ORM\Column(type: Types::JSON)]
    private array $executionLog = []; // [{'garden_id': 1, 'tile_id': 2, 'opcode': 'LDC', 'result': ...}, ...]

    #[ORM\Column(type: Types::JSON)]
    private array $networkEffects = []; // [{'user': 'user@example.com', 'impact': 1}, ...]

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->executionDate = new \DateTimeImmutable();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWeekNumber(): int
    {
        return $this->weekNumber;
    }

    public function setWeekNumber(int $weekNumber): static
    {
        $this->weekNumber = $weekNumber;

        return $this;
    }

    public function getExecutionDate(): \DateTimeImmutable
    {
        return $this->executionDate;
    }

    public function setExecutionDate(\DateTimeImmutable $executionDate): static
    {
        $this->executionDate = $executionDate;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getExecutionLog(): array
    {
        return $this->executionLog;
    }

    public function setExecutionLog(array $executionLog): static
    {
        $this->executionLog = $executionLog;

        return $this;
    }

    public function getNetworkEffects(): array
    {
        return $this->networkEffects;
    }

    public function setNetworkEffects(array $networkEffects): static
    {
        $this->networkEffects = $networkEffects;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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
