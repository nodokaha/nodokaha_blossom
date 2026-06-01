<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EventPostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\EventComment;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventPostRepository::class)]
class EventPost
{
    public const CONTENT_TYPE_PROP = 'prop';
    public const CONTENT_TYPE_WORLD = 'world';
    public const CONTENT_TYPE_AVATAR = 'avatar';

    /** @return array<string, string> */
    public static function contentTypeChoices(): array
    {
        return [
            'Prop' => self::CONTENT_TYPE_PROP,
            'World' => self::CONTENT_TYPE_WORLD,
            'Avatar' => self::CONTENT_TYPE_AVATAR,
        ];
    }

    /** @return array<string, string> */
    public static function contentTypeLabels(): array
    {
        return [
            self::CONTENT_TYPE_PROP => 'Prop',
            self::CONTENT_TYPE_WORLD => 'World',
            self::CONTENT_TYPE_AVATAR => 'Avatar',
        ];
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 140)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 140)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 5000)]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: [self::CONTENT_TYPE_PROP, self::CONTENT_TYPE_WORLD, self::CONTENT_TYPE_AVATAR])]
    private string $contentType = self::CONTENT_TYPE_PROP;

    #[ORM\Column(length: 80)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 80)]
    private ?string $authorName = null;

    #[ORM\Column]
    private \DateTimeImmutable $publishedAt;

    /**
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON)]
    #[Assert\All([new Assert\Type('string'), new Assert\Length(max: 255)])]
    private array $relatedAssets = [];

    /**
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON)]
    #[Assert\All([new Assert\Type('string'), new Assert\Length(max: 40)])]
    private array $tags = [];

    /**
     * @var Collection<int, EventComment>
     */
    #[ORM\OneToMany(mappedBy: 'post', targetEntity: EventComment::class, cascade: ['remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['publishedAt' => 'ASC'])]
    private Collection $comments;

    public function __construct()
    {
        $this->publishedAt = new \DateTimeImmutable();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function setContentType(string $contentType): self
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function getContentTypeLabel(): string
    {
        return self::contentTypeLabels()[$this->contentType] ?? $this->contentType;
    }

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function setAuthorName(string $authorName): self
    {
        $this->authorName = $authorName;

        return $this;
    }

    public function getPublishedAt(): \DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeImmutable $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /** @return list<string> */
    public function getRelatedAssets(): array
    {
        return $this->relatedAssets;
    }

    /** @param list<string> $relatedAssets */
    public function setRelatedAssets(array $relatedAssets): self
    {
        $this->relatedAssets = $this->normalizeList($relatedAssets);

        return $this;
    }

    /** @return list<string> */
    public function getTags(): array
    {
        return $this->tags;
    }

    /** @param list<string> $tags */
    public function setTags(array $tags): self
    {
        $this->tags = $this->normalizeList($tags);

        return $this;
    }

    /**
     * @return Collection<int, EventComment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(EventComment $comment): self
    {
        if (! $this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(EventComment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @param array<mixed> $items
     * @return list<string>
     */
    private function normalizeList(array $items): array
    {
        $normalized = [];
        foreach ($items as $item) {
            $value = trim((string) $item);
            if ($value !== '' && ! in_array($value, $normalized, true)) {
                $normalized[] = $value;
            }
        }

        return $normalized;
    }
}
