<?php

namespace App\Entity;

use App\Enum\GrowthContentStatus;
use App\Repository\GrowthContentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GrowthContentRepository::class)]
#[ORM\Table(name: 'growth_content')]
class GrowthContent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: GrowthCampaign::class, inversedBy: 'contents')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?GrowthCampaign $campaign = null;

    #[ORM\Column(length: 32, enumType: GrowthContentStatus::class)]
    private GrowthContentStatus $status = GrowthContentStatus::DRAFT;

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
    private ?string $featuredImage = null;

    #[ORM\Column(type: Types::JSON)]
    private array $categories = [];

    #[ORM\Column(type: Types::JSON)]
    private array $tags = [];

    #[ORM\Column(length: 255)]
    private string $authorDisplayName = 'Growth Factory';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int { return $this->id; }
    public function getCampaign(): ?GrowthCampaign { return $this->campaign; }
    public function setCampaign(GrowthCampaign $campaign): self { $this->campaign = $campaign; return $this; }
    public function getStatus(): GrowthContentStatus { return $this->status; }
    public function setStatus(GrowthContentStatus $status): self { $this->status = $status; $this->touch(); return $this; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = trim($title); $this->touch(); return $this; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): self { $this->slug = trim($slug); $this->touch(); return $this; }
    public function getExcerpt(): string { return $this->excerpt; }
    public function setExcerpt(string $excerpt): self { $this->excerpt = trim($excerpt); $this->touch(); return $this; }
    public function getContentHtml(): string { return $this->contentHtml; }
    public function setContentHtml(string $contentHtml): self { $this->contentHtml = trim($contentHtml); $this->touch(); return $this; }
    public function getMetaTitle(): string { return $this->metaTitle; }
    public function setMetaTitle(string $metaTitle): self { $this->metaTitle = trim($metaTitle); $this->touch(); return $this; }
    public function getMetaDescription(): string { return $this->metaDescription; }
    public function setMetaDescription(string $metaDescription): self { $this->metaDescription = trim($metaDescription); $this->touch(); return $this; }
    public function getFeaturedImage(): ?string { return $this->featuredImage; }
    public function setFeaturedImage(?string $featuredImage): self { $this->featuredImage = $featuredImage ?: null; $this->touch(); return $this; }
    public function getCategories(): array { return $this->categories; }
    public function setCategories(array $categories): self { $this->categories = array_values($categories); $this->touch(); return $this; }
    public function getTags(): array { return $this->tags; }
    public function setTags(array $tags): self { $this->tags = array_values($tags); $this->touch(); return $this; }
    public function getAuthorDisplayName(): string { return $this->authorDisplayName; }
    public function setAuthorDisplayName(string $authorDisplayName): self { $this->authorDisplayName = trim($authorDisplayName) ?: 'Growth Factory'; $this->touch(); return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    private function touch(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
