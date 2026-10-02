<?php

namespace App\Entity;

use App\Repository\PageBlockTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageBlockTranslationRepository::class)]
#[ORM\Table(
    name: 'page_block_translation',
    uniqueConstraints: [new ORM\UniqueConstraint(name: 'uniq_page_block_translation_locale', columns: ['page_block_id', 'locale'])]
)]
#[ORM\HasLifecycleCallbacks]
class PageBlockTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PageBlock::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?PageBlock $pageBlock = null;

    #[ORM\Column(length: 5)]
    private string $locale = SitePageTranslation::LOCALE_FR;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $eyebrow = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $subtitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bodyHtml = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ctaLabel = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $imageAlt = null;

    #[ORM\Column(length: 32)]
    private string $translationStatus = SitePageTranslation::STATUS_DRAFT;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $sourceContentHash = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $translatedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $reviewedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $reviewedBy = null;

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
    public function getPageBlock(): ?PageBlock { return $this->pageBlock; }
    public function setPageBlock(?PageBlock $pageBlock): self { $this->pageBlock = $pageBlock; return $this; }
    public function getLocale(): string { return $this->locale; }
    public function setLocale(string $locale): self { SitePageTranslation::assertSupportedLocale($locale); $this->locale = $locale; return $this; }
    public function getEyebrow(): ?string { return $this->eyebrow; }
    public function setEyebrow(?string $eyebrow): self { $this->eyebrow = $eyebrow; return $this; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(?string $title): self { $this->title = $title; return $this; }
    public function getSubtitle(): ?string { return $this->subtitle; }
    public function setSubtitle(?string $subtitle): self { $this->subtitle = $subtitle; return $this; }
    public function getBodyHtml(): ?string { return $this->bodyHtml; }
    public function setBodyHtml(?string $bodyHtml): self { $this->bodyHtml = $bodyHtml; return $this; }
    public function getCtaLabel(): ?string { return $this->ctaLabel; }
    public function setCtaLabel(?string $ctaLabel): self { $this->ctaLabel = $ctaLabel; return $this; }
    public function getImageAlt(): ?string { return $this->imageAlt; }
    public function setImageAlt(?string $imageAlt): self { $this->imageAlt = $imageAlt; return $this; }
    public function getTranslationStatus(): string { return $this->translationStatus; }
    public function setTranslationStatus(string $translationStatus): self { SitePageTranslation::assertSupportedStatus($translationStatus); $this->translationStatus = $translationStatus; return $this; }
    public function getSourceContentHash(): ?string { return $this->sourceContentHash; }
    public function setSourceContentHash(?string $sourceContentHash): self { $this->sourceContentHash = $sourceContentHash; return $this; }
    public function getTranslatedAt(): ?\DateTimeImmutable { return $this->translatedAt; }
    public function setTranslatedAt(?\DateTimeImmutable $translatedAt): self { $this->translatedAt = $translatedAt; return $this; }
    public function getReviewedAt(): ?\DateTimeImmutable { return $this->reviewedAt; }
    public function setReviewedAt(?\DateTimeImmutable $reviewedAt): self { $this->reviewedAt = $reviewedAt; return $this; }
    public function getReviewedBy(): ?User { return $this->reviewedBy; }
    public function setReviewedBy(?User $reviewedBy): self { $this->reviewedBy = $reviewedBy; return $this; }
}
