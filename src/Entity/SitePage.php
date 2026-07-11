<?php

namespace App\Entity;

use App\Repository\SitePageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SitePageRepository::class)]
#[ORM\Table(name: 'site_page')]
class SitePage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $metaDescription = null;

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
    private ?string $heroImage = null;

    #[ORM\Column(length: 190, nullable: true, unique: true)]
    private ?string $externalId = null;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $publicationStatus = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $canonicalUrl = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $categories = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $tags = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $publicationDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $authorDisplayName = null;

    #[ORM\Column(length: 190, nullable: true)]
    private ?string $sourceCampaignId = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $unpublishedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
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

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

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

    public function getHeroImage(): ?string
    {
        return $this->heroImage;
    }

    public function setHeroImage(?string $heroImage): self
    {
        $this->heroImage = $heroImage;

        return $this;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(?string $externalId): self
    {
        $this->externalId = $externalId;

        return $this;
    }

    public function getPublicationStatus(): ?string
    {
        return $this->publicationStatus;
    }

    public function setPublicationStatus(?string $publicationStatus): self
    {
        $this->publicationStatus = $publicationStatus;

        return $this;
    }

    public function getCanonicalUrl(): ?string
    {
        return $this->canonicalUrl;
    }

    public function setCanonicalUrl(?string $canonicalUrl): self
    {
        $this->canonicalUrl = $canonicalUrl;

        return $this;
    }

    public function getCategories(): array
    {
        return $this->categories ?? [];
    }

    public function setCategories(?array $categories): self
    {
        $this->categories = $categories;

        return $this;
    }

    public function getTags(): array
    {
        return $this->tags ?? [];
    }

    public function setTags(?array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function getPublicationDate(): ?\DateTimeImmutable
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(?\DateTimeImmutable $publicationDate): self
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    public function getAuthorDisplayName(): ?string
    {
        return $this->authorDisplayName;
    }

    public function setAuthorDisplayName(?string $authorDisplayName): self
    {
        $this->authorDisplayName = $authorDisplayName;

        return $this;
    }

    public function getSourceCampaignId(): ?string
    {
        return $this->sourceCampaignId;
    }

    public function setSourceCampaignId(?string $sourceCampaignId): self
    {
        $this->sourceCampaignId = $sourceCampaignId;

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
