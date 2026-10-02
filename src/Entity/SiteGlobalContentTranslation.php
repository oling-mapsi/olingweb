<?php

namespace App\Entity;

use App\Repository\SiteGlobalContentTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SiteGlobalContentTranslationRepository::class)]
#[ORM\Table(name: 'site_global_content_translation', uniqueConstraints: [new ORM\UniqueConstraint(name: 'uniq_site_global_content_translation_locale', columns: ['site_global_content_id', 'locale'])])]
#[ORM\HasLifecycleCallbacks]
class SiteGlobalContentTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: SiteGlobalContent::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?SiteGlobalContent $siteGlobalContent = null;

    #[ORM\Column(length: 5)]
    private string $locale = SitePageTranslation::LOCALE_FR;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bodyHtml = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ctaLabel = null;

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
    public function getSiteGlobalContent(): ?SiteGlobalContent { return $this->siteGlobalContent; }
    public function setSiteGlobalContent(?SiteGlobalContent $siteGlobalContent): self { $this->siteGlobalContent = $siteGlobalContent; return $this; }
    public function getLocale(): string { return $this->locale; }
    public function setLocale(string $locale): self { SitePageTranslation::assertSupportedLocale($locale); $this->locale = $locale; return $this; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(?string $title): self { $this->title = $title; return $this; }
    public function getBodyHtml(): ?string { return $this->bodyHtml; }
    public function setBodyHtml(?string $bodyHtml): self { $this->bodyHtml = $bodyHtml; return $this; }
    public function getCtaLabel(): ?string { return $this->ctaLabel; }
    public function setCtaLabel(?string $ctaLabel): self { $this->ctaLabel = $ctaLabel; return $this; }
    public function getTranslationStatus(): string { return $this->translationStatus; }
    public function setTranslationStatus(string $translationStatus): self { SitePageTranslation::assertSupportedStatus($translationStatus); $this->translationStatus = $translationStatus; return $this; }
    public function getSourceContentHash(): ?string { return $this->sourceContentHash; }
    public function setSourceContentHash(?string $sourceContentHash): self { $this->sourceContentHash = $sourceContentHash; return $this; }
}
