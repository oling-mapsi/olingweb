<?php

namespace App\Entity;

use App\Repository\SitePageRevisionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SitePageRevisionRepository::class)]
#[ORM\Table(
    name: 'site_page_revision',
    uniqueConstraints: [new ORM\UniqueConstraint(name: 'uniq_site_page_revision_number', columns: ['site_page_id', 'revision_number'])]
)]
class SitePageRevision
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: SitePage::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?SitePage $sitePage = null;

    #[ORM\Column]
    private int $revisionNumber = 1;

    #[ORM\Column(length: 32)]
    private string $revisionState = 'draft';

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(length: 255)]
    private string $slug = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $excerpt = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $contentHtml = '';

    #[ORM\Column(length: 255)]
    private string $metaTitle = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $metaDescription = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $canonicalUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $featuredImage = null;

    #[ORM\Column(type: Types::JSON)]
    private array $categories = [];

    #[ORM\Column(type: Types::JSON)]
    private array $tags = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $publicationDate = null;

    #[ORM\Column(length: 32)]
    private string $status = 'draft';

    #[ORM\Column(length: 255)]
    private string $authorDisplayName = '';

    #[ORM\Column(length: 190)]
    private string $sourceCampaignId = '';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSitePage(): ?SitePage
    {
        return $this->sitePage;
    }

    public function setSitePage(SitePage $sitePage): self
    {
        $this->sitePage = $sitePage;

        return $this;
    }

    public function getRevisionNumber(): int
    {
        return $this->revisionNumber;
    }

    public function setRevisionNumber(int $revisionNumber): self
    {
        $this->revisionNumber = $revisionNumber;

        return $this;
    }

    public function getRevisionState(): string
    {
        return $this->revisionState;
    }

    public function setRevisionState(string $revisionState): self
    {
        $this->revisionState = $revisionState;

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

    public function getExcerpt(): string
    {
        return $this->excerpt;
    }

    public function setExcerpt(string $excerpt): self
    {
        $this->excerpt = $excerpt;

        return $this;
    }

    public function getContentHtml(): string
    {
        return $this->contentHtml;
    }

    public function setContentHtml(string $contentHtml): self
    {
        $this->contentHtml = $contentHtml;

        return $this;
    }

    public function getMetaTitle(): string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(string $metaTitle): self
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

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

    public function getFeaturedImage(): ?string
    {
        return $this->featuredImage;
    }

    public function setFeaturedImage(?string $featuredImage): self
    {
        $this->featuredImage = $featuredImage;

        return $this;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function setCategories(array $categories): self
    {
        $this->categories = $categories;

        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function getPublicationDate(): \DateTimeImmutable
    {
        return $this->publicationDate ?? new \DateTimeImmutable();
    }

    public function setPublicationDate(\DateTimeImmutable $publicationDate): self
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getAuthorDisplayName(): string
    {
        return $this->authorDisplayName;
    }

    public function setAuthorDisplayName(string $authorDisplayName): self
    {
        $this->authorDisplayName = $authorDisplayName;

        return $this;
    }

    public function getSourceCampaignId(): string
    {
        return $this->sourceCampaignId;
    }

    public function setSourceCampaignId(string $sourceCampaignId): self
    {
        $this->sourceCampaignId = $sourceCampaignId;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt ?? new \DateTimeImmutable();
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
