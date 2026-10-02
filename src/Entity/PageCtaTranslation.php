<?php

namespace App\Entity;

use App\Repository\PageCtaTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageCtaTranslationRepository::class)]
#[ORM\Table(name: 'page_cta_translation', uniqueConstraints: [new ORM\UniqueConstraint(name: 'uniq_page_cta_translation_locale', columns: ['page_cta_id', 'locale'])])]
#[ORM\HasLifecycleCallbacks]
class PageCtaTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PageCta::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?PageCta $pageCta = null;

    #[ORM\Column(length: 5)]
    private string $locale = SitePageTranslation::LOCALE_FR;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $label = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $body = null;

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
    public function getPageCta(): ?PageCta { return $this->pageCta; }
    public function setPageCta(?PageCta $pageCta): self { $this->pageCta = $pageCta; return $this; }
    public function getLocale(): string { return $this->locale; }
    public function setLocale(string $locale): self { SitePageTranslation::assertSupportedLocale($locale); $this->locale = $locale; return $this; }
    public function getLabel(): ?string { return $this->label; }
    public function setLabel(?string $label): self { $this->label = $label; return $this; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(?string $title): self { $this->title = $title; return $this; }
    public function getBody(): ?string { return $this->body; }
    public function setBody(?string $body): self { $this->body = $body; return $this; }
    public function getTranslationStatus(): string { return $this->translationStatus; }
    public function setTranslationStatus(string $translationStatus): self { SitePageTranslation::assertSupportedStatus($translationStatus); $this->translationStatus = $translationStatus; return $this; }
    public function getSourceContentHash(): ?string { return $this->sourceContentHash; }
    public function setSourceContentHash(?string $sourceContentHash): self { $this->sourceContentHash = $sourceContentHash; return $this; }
}
