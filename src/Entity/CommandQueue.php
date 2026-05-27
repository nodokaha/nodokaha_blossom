<?php

namespace App\Entity;

use App\Repository\CommandQueueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandQueueRepository::class)]
class CommandQueue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Garden $garden = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Tile $tile = null;

    #[ORM\Column(type: Types::JSON)]
    private array $command = []; // {'opcode': 'LDC', 'args': '5'}

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $insertedDate;

    #[ORM\Column(nullable: true)]
    private ?int $executionWeek = null;

    #[ORM\Column(length: 50)]
    private string $status = 'pending'; // pending, queued, executed, failed

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $executionResult = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->insertedDate = new \DateTimeImmutable();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getGarden(): ?Garden
    {
        return $this->garden;
    }

    public function setGarden(?Garden $garden): static
    {
        $this->garden = $garden;

        return $this;
    }

    public function getTile(): ?Tile
    {
        return $this->tile;
    }

    public function setTile(?Tile $tile): static
    {
        $this->tile = $tile;

        return $this;
    }

    public function getCommand(): array
    {
        return $this->command;
    }

    public function setCommand(array $command): static
    {
        $this->command = $command;

        return $this;
    }

    public function getInsertedDate(): \DateTimeImmutable
    {
        return $this->insertedDate;
    }

    public function setInsertedDate(\DateTimeImmutable $insertedDate): static
    {
        $this->insertedDate = $insertedDate;

        return $this;
    }

    public function getExecutionWeek(): ?int
    {
        return $this->executionWeek;
    }

    public function setExecutionWeek(?int $executionWeek): static
    {
        $this->executionWeek = $executionWeek;

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

    public function getExecutionResult(): ?array
    {
        return $this->executionResult;
    }

    public function setExecutionResult(?array $executionResult): static
    {
        $this->executionResult = $executionResult;

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
