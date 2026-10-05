<?php

namespace App\Entity;

use App\Enum\GrowthDestination;
use App\Enum\GrowthPublicationStatus;
use App\Repository\GrowthPublicationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GrowthPublicationRepository::class)]
#[ORM\Table(name: 'growth_publication')]
class GrowthPublication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: GrowthCampaign::class, inversedBy: 'publications')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?GrowthCampaign $campaign = null;

    #[ORM\ManyToOne(targetEntity: GrowthContent::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?GrowthContent $content = null;

    #[ORM\Column(length: 32, enumType: GrowthDestination::class)]
    private GrowthDestination $destination = GrowthDestination::OLING_PUBLIC;

    #[ORM\Column(length: 32, enumType: GrowthPublicationStatus::class)]
    private GrowthPublicationStatus $status = GrowthPublicationStatus::PENDING;

    #[ORM\Column(length: 190, nullable: true)]
    private ?string $externalIdentifier = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $lastError = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getCampaign(): ?GrowthCampaign { return $this->campaign; }
    public function setCampaign(GrowthCampaign $campaign): self { $this->campaign = $campaign; return $this; }
    public function getContent(): ?GrowthContent { return $this->content; }
    public function setContent(GrowthContent $content): self { $this->content = $content; return $this; }
    public function getDestination(): GrowthDestination { return $this->destination; }
    public function setDestination(GrowthDestination $destination): self { $this->destination = $destination; return $this; }
    public function getStatus(): GrowthPublicationStatus { return $this->status; }
    public function setStatus(GrowthPublicationStatus $status): self { $this->status = $status; return $this; }
    public function getExternalIdentifier(): ?string { return $this->externalIdentifier; }
    public function setExternalIdentifier(?string $externalIdentifier): self { $this->externalIdentifier = $externalIdentifier ?: null; return $this; }
    public function getPublishedAt(): ?\DateTimeImmutable { return $this->publishedAt; }
    public function setPublishedAt(?\DateTimeImmutable $publishedAt): self { $this->publishedAt = $publishedAt; return $this; }
    public function getLastError(): ?string { return $this->lastError; }
    public function setLastError(?string $lastError): self { $this->lastError = $lastError ?: null; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
