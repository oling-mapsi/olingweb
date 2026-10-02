<?php

namespace App\Entity;

use App\Repository\FaqItemTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FaqItemTranslationRepository::class)]
#[ORM\Table(name: 'faq_item_translation', uniqueConstraints: [new ORM\UniqueConstraint(name: 'uniq_faq_item_translation_locale', columns: ['faq_item_id', 'locale'])])]
#[ORM\HasLifecycleCallbacks]
class FaqItemTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: FaqItem::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?FaqItem $faqItem = null;

    #[ORM\Column(length: 5)]
    private string $locale = SitePageTranslation::LOCALE_FR;

    #[ORM\Column(type: Types::TEXT)]
    private string $question = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $answerHtml = null;

    #[ORM\Column(length: 32)]
    private string $translationStatus = SitePageTranslation::STATUS_DRAFT;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $sourceContentHash = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $reviewedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $reviewedBy = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt ??= $now; }
    #[ORM\PreUpdate]
    public function onPreUpdate(): void { $this->updatedAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getFaqItem(): ?FaqItem { return $this->faqItem; }
    public function setFaqItem(?FaqItem $faqItem): self { $this->faqItem = $faqItem; return $this; }
    public function getLocale(): string { return $this->locale; }
    public function setLocale(string $locale): self { SitePageTranslation::assertSupportedLocale($locale); $this->locale = $locale; return $this; }
    public function getQuestion(): string { return $this->question; }
    public function setQuestion(string $question): self { $this->question = $question; return $this; }
    public function getAnswerHtml(): ?string { return $this->answerHtml; }
    public function setAnswerHtml(?string $answerHtml): self { $this->answerHtml = $answerHtml; return $this; }
    public function getTranslationStatus(): string { return $this->translationStatus; }
    public function setTranslationStatus(string $translationStatus): self { SitePageTranslation::assertSupportedStatus($translationStatus); $this->translationStatus = $translationStatus; return $this; }
    public function getSourceContentHash(): ?string { return $this->sourceContentHash; }
    public function setSourceContentHash(?string $sourceContentHash): self { $this->sourceContentHash = $sourceContentHash; return $this; }
    public function getReviewedAt(): ?\DateTimeImmutable { return $this->reviewedAt; }
    public function setReviewedAt(?\DateTimeImmutable $reviewedAt): self { $this->reviewedAt = $reviewedAt; return $this; }
    public function getReviewedBy(): ?User { return $this->reviewedBy; }
    public function setReviewedBy(?User $reviewedBy): self { $this->reviewedBy = $reviewedBy; return $this; }
}
