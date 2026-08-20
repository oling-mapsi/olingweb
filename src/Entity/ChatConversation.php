<?php

namespace App\Entity;

use App\Repository\ChatConversationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChatConversationRepository::class)]
#[ORM\Table(name: 'chat_conversation')]
#[ORM\HasLifecycleCallbacks]
class ChatConversation
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_LEAD_PENDING = 'lead_pending';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_EXPIRED = 'expired';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64, unique: true)]
    private ?string $publicToken = null;

    #[ORM\Column(length: 32)]
    private string $status = self::STATUS_ACTIVE;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sourcePath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sourceUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $referrer = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $locale = 'fr';

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $ipHash = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $userAgentHash = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $qualification = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $summaryShort = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $summaryLong = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $lastMessageAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $submittedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $retentionPurgeAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $consentAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $emailSentAt = null;

    #[ORM\OneToMany(mappedBy: 'conversation', targetEntity: ChatMessage::class, orphanRemoval: true, cascade: ['persist'])]
    #[ORM\OrderBy(['sequenceNumber' => 'ASC'])]
    private Collection $messages;

    #[ORM\OneToOne(mappedBy: 'conversation', targetEntity: ChatLead::class, cascade: ['persist', 'remove'])]
    private ?ChatLead $lead = null;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function initializeTimestamps(): void
    {
        $now = new \DateTimeImmutable();
        $this->startedAt ??= $now;
        $this->lastMessageAt ??= $now;
        $this->expiresAt ??= $now->modify('+30 days');
        $this->retentionPurgeAt ??= $now->modify('+30 days');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicToken(): ?string
    {
        return $this->publicToken;
    }

    public function setPublicToken(string $publicToken): self
    {
        $this->publicToken = $publicToken;

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

    public function getSourcePath(): ?string
    {
        return $this->sourcePath;
    }

    public function setSourcePath(?string $sourcePath): self
    {
        $this->sourcePath = $sourcePath;

        return $this;
    }

    public function getSourceUrl(): ?string
    {
        return $this->sourceUrl;
    }

    public function setSourceUrl(?string $sourceUrl): self
    {
        $this->sourceUrl = $sourceUrl;

        return $this;
    }

    public function getReferrer(): ?string
    {
        return $this->referrer;
    }

    public function setReferrer(?string $referrer): self
    {
        $this->referrer = $referrer;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getIpHash(): ?string
    {
        return $this->ipHash;
    }

    public function setIpHash(?string $ipHash): self
    {
        $this->ipHash = $ipHash;

        return $this;
    }

    public function getUserAgentHash(): ?string
    {
        return $this->userAgentHash;
    }

    public function setUserAgentHash(?string $userAgentHash): self
    {
        $this->userAgentHash = $userAgentHash;

        return $this;
    }

    public function getQualification(): array
    {
        return $this->qualification ?? [];
    }

    public function setQualification(?array $qualification): self
    {
        $this->qualification = $qualification;

        return $this;
    }

    public function getSummaryShort(): ?string
    {
        return $this->summaryShort;
    }

    public function setSummaryShort(?string $summaryShort): self
    {
        $this->summaryShort = $summaryShort;

        return $this;
    }

    public function getSummaryLong(): ?string
    {
        return $this->summaryLong;
    }

    public function setSummaryLong(?string $summaryLong): self
    {
        $this->summaryLong = $summaryLong;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getLastMessageAt(): ?\DateTimeImmutable
    {
        return $this->lastMessageAt;
    }

    public function setLastMessageAt(\DateTimeImmutable $lastMessageAt): self
    {
        $this->lastMessageAt = $lastMessageAt;

        return $this;
    }

    public function getSubmittedAt(): ?\DateTimeImmutable
    {
        return $this->submittedAt;
    }

    public function setSubmittedAt(?\DateTimeImmutable $submittedAt): self
    {
        $this->submittedAt = $submittedAt;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getRetentionPurgeAt(): ?\DateTimeImmutable
    {
        return $this->retentionPurgeAt;
    }

    public function setRetentionPurgeAt(\DateTimeImmutable $retentionPurgeAt): self
    {
        $this->retentionPurgeAt = $retentionPurgeAt;

        return $this;
    }

    public function getConsentAt(): ?\DateTimeImmutable
    {
        return $this->consentAt;
    }

    public function setConsentAt(?\DateTimeImmutable $consentAt): self
    {
        $this->consentAt = $consentAt;

        return $this;
    }

    public function getEmailSentAt(): ?\DateTimeImmutable
    {
        return $this->emailSentAt;
    }

    public function setEmailSentAt(?\DateTimeImmutable $emailSentAt): self
    {
        $this->emailSentAt = $emailSentAt;

        return $this;
    }

    /**
     * @return Collection<int, ChatMessage>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(ChatMessage $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setConversation($this);
        }

        return $this;
    }

    public function removeMessage(ChatMessage $message): self
    {
        if ($this->messages->removeElement($message) && $message->getConversation() === $this) {
            $message->setConversation(null);
        }

        return $this;
    }

    public function getLead(): ?ChatLead
    {
        return $this->lead;
    }

    public function setLead(?ChatLead $lead): self
    {
        if ($lead && $lead->getConversation() !== $this) {
            $lead->setConversation($this);
        }

        $this->lead = $lead;

        return $this;
    }
}
