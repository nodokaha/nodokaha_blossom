<?php

namespace App\Entity;

use App\Repository\TileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TileRepository::class)]
class Tile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tiles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Garden $garden = null;

    #[ORM\Column]
    private int $x = 0;

    #[ORM\Column]
    private int $y = 0;

    #[ORM\Column(length: 50)]
    private string $role = 'field'; // field, seed, flower, worker

    #[ORM\Column(type: Types::JSON)]
    private array $stackData = []; // [{'opcode': 'LDC', 'args': '5'}, ...]

    #[ORM\Column(type: Types::JSON)]
    private array $stackState = []; // 実行時のスタック状態

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getX(): int
    {
        return $this->x;
    }

    public function setX(int $x): static
    {
        $this->x = $x;

        return $this;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function setY(int $y): static
    {
        $this->y = $y;

        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getStackData(): array
    {
        return $this->stackData;
    }

    public function setStackData(array $stackData): static
    {
        $this->stackData = $stackData;

        return $this;
    }

    public function getStackState(): array
    {
        return $this->stackState;
    }

    public function setStackState(array $stackState): static
    {
        $this->stackState = $stackState;

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
