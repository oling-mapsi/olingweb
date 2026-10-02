<?php

namespace App\Entity;

use App\Repository\RelatedLinkTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RelatedLinkTranslationRepository::class)]
#[ORM\Table(name: 'related_link_translation', uniqueConstraints: [new ORM\UniqueConstraint(name: 'uniq_related_link_translation_locale', columns: ['related_link_id', 'locale'])])]
#[ORM\HasLifecycleCallbacks]
class RelatedLinkTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: RelatedLink::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?RelatedLink $relatedLink = null;

    #[ORM\Column(length: 5)]
    private string $locale = SitePageTranslation::LOCALE_FR;

    #[ORM\Column(length: 255)]
    private string $label = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 32)]
    private string $translationStatus = SitePageTranslation::STATUS_DRAFT;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $sourceContentHash = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt ??= $now; }
    #[ORM\PreUpdate]
    public function onPreUpdate(): void { $this->updatedAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getRelatedLink(): ?RelatedLink { return $this->relatedLink; }
    public function setRelatedLink(?RelatedLink $relatedLink): self { $this->relatedLink = $relatedLink; return $this; }
    public function getLocale(): string { return $this->locale; }
    public function setLocale(string $locale): self { SitePageTranslation::assertSupportedLocale($locale); $this->locale = $locale; return $this; }
    public function getLabel(): string { return $this->label; }
    public function setLabel(string $label): self { $this->label = $label; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    public function getTranslationStatus(): string { return $this->translationStatus; }
    public function setTranslationStatus(string $translationStatus): self { SitePageTranslation::assertSupportedStatus($translationStatus); $this->translationStatus = $translationStatus; return $this; }
    public function getSourceContentHash(): ?string { return $this->sourceContentHash; }
    public function setSourceContentHash(?string $sourceContentHash): self { $this->sourceContentHash = $sourceContentHash; return $this; }
}
