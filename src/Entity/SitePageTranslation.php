<?php

namespace App\Entity;

use App\Repository\SitePageTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SitePageTranslationRepository::class)]
#[ORM\Table(
    name: 'site_page_translation',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'uniq_site_page_translation_locale', columns: ['site_page_id', 'locale']),
        new ORM\UniqueConstraint(name: 'uniq_site_page_translation_locale_slug', columns: ['locale', 'slug']),
    ],
    indexes: [
        new ORM\Index(name: 'idx_site_page_translation_status', columns: ['locale', 'translation_status']),
    ]
)]
#[ORM\HasLifecycleCallbacks]
class SitePageTranslation
{
    public const LOCALE_FR = 'fr';
    public const LOCALE_EN = 'en';
    public const LOCALE_ES = 'es';
    public const SUPPORTED_LOCALES = [self::LOCALE_FR, self::LOCALE_EN, self::LOCALE_ES];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_AI_TRANSLATED = 'ai_translated';
    public const STATUS_TO_REVIEW = 'to_review';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_PUBLISHED = 'published';
    public const SUPPORTED_STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_AI_TRANSLATED,
        self::STATUS_TO_REVIEW,
        self::STATUS_REVIEWED,
        self::STATUS_PUBLISHED,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: SitePage::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?SitePage $sitePage = null;

    #[ORM\Column(length: 5)]
    private string $locale = self::LOCALE_FR;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(length: 100)]
    private string $slug = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $heroBadge = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $heroTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $heroIntro = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $heroSideHtml = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bodyHtml = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $seoTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $seoDescription = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ogTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $ogDescription = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $imageAlt = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $structuredData = null;

    #[ORM\Column(length: 32)]
    private string $translationStatus = self::STATUS_DRAFT;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $sourceContentHash = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $sourceUpdatedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $translatedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $reviewedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $reviewedBy = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $unpublishedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    public static function assertSupportedLocale(string $locale): void
    {
        if (!in_array($locale, self::SUPPORTED_LOCALES, true)) {
            throw new \InvalidArgumentException(sprintf('Unsupported locale "%s".', $locale));
        }
    }

    public static function assertSupportedStatus(string $status): void
    {
        if (!in_array($status, self::SUPPORTED_STATUSES, true)) {
            throw new \InvalidArgumentException(sprintf('Unsupported translation status "%s".', $status));
        }
    }

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSitePage(): ?SitePage
    {
        return $this->sitePage;
    }

    public function setSitePage(?SitePage $sitePage): self
    {
        $this->sitePage = $sitePage;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        self::assertSupportedLocale($locale);
        $this->locale = $locale;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getHeroBadge(): ?string
    {
        return $this->heroBadge;
    }

    public function setHeroBadge(?string $heroBadge): self
    {
        $this->heroBadge = $heroBadge;

        return $this;
    }

    public function getHeroTitle(): ?string
    {
        return $this->heroTitle;
    }

    public function setHeroTitle(?string $heroTitle): self
    {
        $this->heroTitle = $heroTitle;

        return $this;
    }

    public function getHeroIntro(): ?string
    {
        return $this->heroIntro;
    }

    public function setHeroIntro(?string $heroIntro): self
    {
        $this->heroIntro = $heroIntro;

        return $this;
    }

    public function getHeroSideHtml(): ?string
    {
        return $this->heroSideHtml;
    }

    public function setHeroSideHtml(?string $heroSideHtml): self
    {
        $this->heroSideHtml = $heroSideHtml;

        return $this;
    }

    public function getBodyHtml(): ?string
    {
        return $this->bodyHtml;
    }

    public function setBodyHtml(?string $bodyHtml): self
    {
        $this->bodyHtml = $bodyHtml;

        return $this;
    }

    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    public function setSeoTitle(?string $seoTitle): self
    {
        $this->seoTitle = $seoTitle;

        return $this;
    }

    public function getSeoDescription(): ?string
    {
        return $this->seoDescription;
    }

    public function setSeoDescription(?string $seoDescription): self
    {
        $this->seoDescription = $seoDescription;

        return $this;
    }

    public function getOgTitle(): ?string
    {
        return $this->ogTitle;
    }

    public function setOgTitle(?string $ogTitle): self
    {
        $this->ogTitle = $ogTitle;

        return $this;
    }

    public function getOgDescription(): ?string
    {
        return $this->ogDescription;

    }

    public function setOgDescription(?string $ogDescription): self
    {
        $this->ogDescription = $ogDescription;

        return $this;
    }

    public function getImageAlt(): ?string
    {
        return $this->imageAlt;
    }

    public function setImageAlt(?string $imageAlt): self
    {
        $this->imageAlt = $imageAlt;

        return $this;
    }

    public function getStructuredData(): ?array
    {
        return $this->structuredData;
    }

    public function setStructuredData(?array $structuredData): self
    {
        $this->structuredData = $structuredData;

        return $this;
    }

    public function getTranslationStatus(): string
    {
        return $this->translationStatus;
    }

    public function setTranslationStatus(string $translationStatus): self
    {
        self::assertSupportedStatus($translationStatus);
        $this->translationStatus = $translationStatus;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->translationStatus === self::STATUS_PUBLISHED && $this->publishedAt !== null && $this->unpublishedAt === null;
    }

    public function getSourceContentHash(): ?string
    {
        return $this->sourceContentHash;
    }

    public function setSourceContentHash(?string $sourceContentHash): self
    {
        $this->sourceContentHash = $sourceContentHash;

        return $this;
    }

    public function isOutdated(string $currentFrenchSourceHash): bool
    {
        return $this->sourceContentHash !== null && !hash_equals($this->sourceContentHash, $currentFrenchSourceHash);
    }

    public function getSourceUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->sourceUpdatedAt;
    }

    public function setSourceUpdatedAt(?\DateTimeImmutable $sourceUpdatedAt): self
    {
        $this->sourceUpdatedAt = $sourceUpdatedAt;

        return $this;
    }

    public function getTranslatedAt(): ?\DateTimeImmutable
    {
        return $this->translatedAt;
    }

    public function setTranslatedAt(?\DateTimeImmutable $translatedAt): self
    {
        $this->translatedAt = $translatedAt;

        return $this;
    }

    public function getReviewedAt(): ?\DateTimeImmutable
    {
        return $this->reviewedAt;
    }

    public function setReviewedAt(?\DateTimeImmutable $reviewedAt): self
    {
        $this->reviewedAt = $reviewedAt;

        return $this;
    }

    public function getReviewedBy(): ?User
    {
        return $this->reviewedBy;
    }

    public function setReviewedBy(?User $reviewedBy): self
    {
        $this->reviewedBy = $reviewedBy;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getUnpublishedAt(): ?\DateTimeImmutable
    {
        return $this->unpublishedAt;
    }

    public function setUnpublishedAt(?\DateTimeImmutable $unpublishedAt): self
    {
        $this->unpublishedAt = $unpublishedAt;

        return $this;
    }
}
