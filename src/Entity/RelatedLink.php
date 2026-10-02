<?php

namespace App\Entity;

use App\Repository\RelatedLinkRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RelatedLinkRepository::class)]
#[ORM\Table(name: 'related_link')]
#[ORM\HasLifecycleCallbacks]
class RelatedLink
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: SitePage::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?SitePage $sourcePage = null;

    #[ORM\ManyToOne(targetEntity: SitePage::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?SitePage $targetPage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $externalUrl = null;

    #[ORM\Column]
    private int $sortOrder = 0;

    #[ORM\Column]
    private bool $enabled = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt ??= $now; }
    #[ORM\PreUpdate]
    public function onPreUpdate(): void { $this->updatedAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getSourcePage(): ?SitePage { return $this->sourcePage; }
    public function setSourcePage(?SitePage $sourcePage): self { $this->sourcePage = $sourcePage; return $this; }
    public function getTargetPage(): ?SitePage { return $this->targetPage; }
    public function setTargetPage(?SitePage $targetPage): self { $this->targetPage = $targetPage; return $this; }
    public function getExternalUrl(): ?string { return $this->externalUrl; }
    public function setExternalUrl(?string $externalUrl): self { $this->externalUrl = $externalUrl; return $this; }
    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $sortOrder): self { $this->sortOrder = $sortOrder; return $this; }
    public function isEnabled(): bool { return $this->enabled; }
    public function setEnabled(bool $enabled): self { $this->enabled = $enabled; return $this; }
}
