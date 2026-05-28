<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AssetFileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssetFileRepository::class)]
#[ORM\Table(name: 'asset_file')]
class AssetFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 190, unique: true)]
    private string $storageKey;

    #[ORM\Column(length: 255)]
    private string $originalName;

    #[ORM\Column(length: 120)]
    private string $mimeType;

    #[ORM\Column]
    private int $size;

    #[ORM\Column(length: 64)]
    private string $encryptionKey;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getStorageKey(): string { return $this->storageKey; }
    public function setStorageKey(string $storageKey): self { $this->storageKey = $storageKey; return $this; }
    public function getOriginalName(): string { return $this->originalName; }
    public function setOriginalName(string $originalName): self { $this->originalName = $originalName; return $this; }
    public function getMimeType(): string { return $this->mimeType; }
    public function setMimeType(string $mimeType): self { $this->mimeType = $mimeType; return $this; }
    public function getSize(): int { return $this->size; }
    public function setSize(int $size): self { $this->size = $size; return $this; }
    public function getEncryptionKey(): string { return $this->encryptionKey; }
    public function setEncryptionKey(string $encryptionKey): self { $this->encryptionKey = $encryptionKey; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
