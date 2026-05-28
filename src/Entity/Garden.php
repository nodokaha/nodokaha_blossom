<?php

namespace App\Entity;

use App\Repository\GardenRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GardenRepository::class)]
class Garden
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'gardens')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(length: 120)]
    private string $name = '';

    #[ORM\Column(type: 'text')]
    private string $description = '';

    /**
     * @var Collection<int, Tile>
     */
    #[ORM\OneToMany(mappedBy: 'garden', targetEntity: Tile::class, orphanRemoval: true)]
    private Collection $tiles;

    public function __construct()
    {
        $this->tiles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = trim($name);

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = trim($description);

        return $this;
    }

    /**
     * @return Collection<int, Tile>
     */
    public function getTiles(): Collection
    {
        return $this->tiles;
    }

    public function addTile(Tile $tile): static
    {
        if (!$this->tiles->contains($tile)) {
            $this->tiles->add($tile);
            $tile->setGarden($this);
        }

        return $this;
    }

    public function removeTile(Tile $tile): static
    {
        if ($this->tiles->removeElement($tile) && $tile->getGarden() === $this) {
            $tile->setGarden(null);
        }

        return $this;
    }
}
