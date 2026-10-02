<?php

namespace App\Entity;

use App\Repository\PageBlockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageBlockRepository::class)]
#[ORM\Table(name: 'page_block')]
#[ORM\HasLifecycleCallbacks]
class PageBlock
{
    public const TYPE_TEXT = 'text';
    public const TYPE_TEXT_IMAGE = 'text_image';
    public const TYPE_FEATURE_LIST = 'feature_list';
    public const TYPE_KEY_FIGURES = 'key_figures';
    public const TYPE_PROOF = 'proof';
    public const TYPE_QUOTE = 'quote';
    public const TYPE_LOGOS = 'logos';
    public const TYPE_CARDS = 'cards';
    public const TYPE_MAP = 'map';
    public const TYPE_CTA = 'cta';
    public const TYPE_CUSTOM = 'custom';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: SitePage::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?SitePage $sitePage = null;

    #[ORM\Column(length: 32)]
    private string $blockType = self::TYPE_TEXT;

    #[ORM\Column]
    private int $sortOrder = 0;

    #[ORM\Column]
    private bool $enabled = true;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $layout = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $variant = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $icon = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $technicalConfig = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTimeImmutable();
        $this->createdAt ??= $now;
        $this->updatedAt ??= $now;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getSitePage(): ?SitePage { return $this->sitePage; }
    public function setSitePage(?SitePage $sitePage): self { $this->sitePage = $sitePage; return $this; }
    public function getBlockType(): string { return $this->blockType; }
    public function setBlockType(string $blockType): self { $this->blockType = $blockType; return $this; }
    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $sortOrder): self { $this->sortOrder = $sortOrder; return $this; }
    public function isEnabled(): bool { return $this->enabled; }
    public function setEnabled(bool $enabled): self { $this->enabled = $enabled; return $this; }
    public function getLayout(): ?string { return $this->layout; }
    public function setLayout(?string $layout): self { $this->layout = $layout; return $this; }
    public function getVariant(): ?string { return $this->variant; }
    public function setVariant(?string $variant): self { $this->variant = $variant; return $this; }
    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): self { $this->image = $image; return $this; }
    public function getIcon(): ?string { return $this->icon; }
    public function setIcon(?string $icon): self { $this->icon = $icon; return $this; }
    public function getTechnicalConfig(): ?array { return $this->technicalConfig; }
    public function setTechnicalConfig(?array $technicalConfig): self { $this->technicalConfig = $technicalConfig; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
}
