<?php

namespace App\Entity;

use App\Repository\LocalizedSlugHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocalizedSlugHistoryRepository::class)]
#[ORM\Table(
    name: 'localized_slug_history',
    indexes: [
        new ORM\Index(name: 'idx_localized_slug_history_lookup', columns: ['resource_type', 'resource_id', 'locale', 'old_slug']),
    ]
)]
class LocalizedSlugHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    private string $resourceType = '';

    #[ORM\Column]
    private int $resourceId = 0;

    #[ORM\Column(length: 5)]
    private string $locale = SitePageTranslation::LOCALE_FR;

    #[ORM\Column(length: 255)]
    private string $oldSlug = '';

    #[ORM\Column(length: 255)]
    private string $newSlug = '';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $changedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $changedBy = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    public function setResourceType(string $resourceType): self
    {
        $this->resourceType = $resourceType;

        return $this;
    }

    public function getResourceId(): int
    {
        return $this->resourceId;
    }

    public function setResourceId(int $resourceId): self
    {
        $this->resourceId = $resourceId;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        SitePageTranslation::assertSupportedLocale($locale);
        $this->locale = $locale;

        return $this;
    }

    public function getOldSlug(): string
    {
        return $this->oldSlug;
    }

    public function setOldSlug(string $oldSlug): self
    {
        $this->oldSlug = $oldSlug;

        return $this;
    }

    public function getNewSlug(): string
    {
        return $this->newSlug;
    }

    public function setNewSlug(string $newSlug): self
    {
        $this->newSlug = $newSlug;

        return $this;
    }

    public function getChangedAt(): ?\DateTimeImmutable
    {
        return $this->changedAt;
    }

    public function setChangedAt(\DateTimeImmutable $changedAt): self
    {
        $this->changedAt = $changedAt;

        return $this;
    }

    public function getChangedBy(): ?User
    {
        return $this->changedBy;
    }

    public function setChangedBy(?User $changedBy): self
    {
        $this->changedBy = $changedBy;

        return $this;
    }
}
