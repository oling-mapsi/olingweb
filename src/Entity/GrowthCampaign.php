<?php

namespace App\Entity;

use App\Enum\GrowthCampaignStatus;
use App\Repository\GrowthCampaignRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GrowthCampaignRepository::class)]
#[ORM\Table(name: 'growth_campaign')]
class GrowthCampaign
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(length: 32, enumType: GrowthCampaignStatus::class)]
    private GrowthCampaignStatus $status = GrowthCampaignStatus::DRAFT;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $createdBy = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, GrowthContent> */
    #[ORM\OneToMany(mappedBy: 'campaign', targetEntity: GrowthContent::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $contents;

    /** @var Collection<int, GrowthPublication> */
    #[ORM\OneToMany(mappedBy: 'campaign', targetEntity: GrowthPublication::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $publications;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->contents = new ArrayCollection();
        $this->publications = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = trim($title); $this->touch(); return $this; }
    public function getStatus(): GrowthCampaignStatus { return $this->status; }
    public function setStatus(GrowthCampaignStatus $status): self { $this->status = $status; $this->touch(); return $this; }
    public function getCreatedBy(): ?string { return $this->createdBy; }
    public function setCreatedBy(?string $createdBy): self { $this->createdBy = $createdBy; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function touch(): void { $this->updatedAt = new \DateTimeImmutable(); }

    /** @return Collection<int, GrowthContent> */
    public function getContents(): Collection { return $this->contents; }
    public function getPrimaryContent(): ?GrowthContent { return $this->contents->first() ?: null; }
    public function addContent(GrowthContent $content): self
    {
        if (!$this->contents->contains($content)) {
            $this->contents->add($content);
            $content->setCampaign($this);
        }
        return $this;
    }

    /** @return Collection<int, GrowthPublication> */
    public function getPublications(): Collection { return $this->publications; }
}
