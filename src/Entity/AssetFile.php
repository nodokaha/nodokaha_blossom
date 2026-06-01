<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AssetFileRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AssetFileRepository::class)]
#[ORM\Table(name: 'asset_file')]
class AssetFile
{
    public const ASSET_TYPE_PROP = 'prop';
    public const ASSET_TYPE_WORLD = 'world';
    public const ASSET_TYPE_AVATAR = 'avatar';

    public const ASSET_TYPE_LABELS = [
        self::ASSET_TYPE_PROP => 'プロップ',
        self::ASSET_TYPE_WORLD => 'ワールド',
        self::ASSET_TYPE_AVATAR => 'アバター',
    ];

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

    #[ORM\Column(length: 20, options: ['default' => self::ASSET_TYPE_PROP])]
    #[Assert\Choice(choices: [self::ASSET_TYPE_PROP, self::ASSET_TYPE_WORLD, self::ASSET_TYPE_AVATAR])]
    private string $assetType = self::ASSET_TYPE_PROP;

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
    public function getAssetType(): string { return $this->assetType; }
    public function setAssetType(string $assetType): self
    {
        if (! self::isValidAssetType($assetType)) {
            throw new \InvalidArgumentException(sprintf('Invalid asset type "%s".', $assetType));
        }

        $this->assetType = $assetType;

        return $this;
    }
    public function getAssetTypeLabel(): string { return self::ASSET_TYPE_LABELS[$this->assetType] ?? $this->assetType; }
    /** @return string[] */
    public static function getAllowedAssetTypes(): array { return array_keys(self::ASSET_TYPE_LABELS); }
    public static function isValidAssetType(mixed $assetType): bool { return is_string($assetType) && in_array($assetType, self::getAllowedAssetTypes(), true); }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
