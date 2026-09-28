<?php

namespace App\Entity;

use App\Repository\ChatMessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChatMessageRepository::class)]
#[ORM\Table(name: 'chat_message')]
class ChatMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ChatConversation $conversation = null;

    #[ORM\Column(length: 16)]
    private ?string $role = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(length: 32)]
    private string $messageType = 'message';

    #[ORM\Column]
    private int $sequenceNumber = 0;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $sourceUrls = null;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $provider = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $model = null;

    #[ORM\Column]
    private bool $fallbackUsed = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ownerUrl = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $selectedDocuments = null;

    #[ORM\Column(nullable: true)]
    private ?int $latencyMs = null;

    #[ORM\Column(nullable: true)]
    private ?int $inputTokens = null;

    #[ORM\Column(nullable: true)]
    private ?int $outputTokens = null;

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $errorCode = null;

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $requestId = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConversation(): ?ChatConversation
    {
        return $this->conversation;
    }

    public function setConversation(?ChatConversation $conversation): self
    {
        $this->conversation = $conversation;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getMessageType(): string
    {
        return $this->messageType;
    }

    public function setMessageType(string $messageType): self
    {
        $this->messageType = $messageType;

        return $this;
    }

    public function getSequenceNumber(): int
    {
        return $this->sequenceNumber;
    }

    public function setSequenceNumber(int $sequenceNumber): self
    {
        $this->sequenceNumber = $sequenceNumber;

        return $this;
    }

    public function getSourceUrls(): array
    {
        return $this->sourceUrls ?? [];
    }

    public function setSourceUrls(?array $sourceUrls): self
    {
        $this->sourceUrls = $sourceUrls;

        return $this;
    }

    public function getProvider(): ?string { return $this->provider; }
    public function setProvider(?string $value): self { $this->provider = $value; return $this; }
    public function getModel(): ?string { return $this->model; }
    public function setModel(?string $value): self { $this->model = $value; return $this; }
    public function isFallbackUsed(): bool { return $this->fallbackUsed; }
    public function setFallbackUsed(bool $value): self { $this->fallbackUsed = $value; return $this; }
    public function getOwnerUrl(): ?string { return $this->ownerUrl; }
    public function setOwnerUrl(?string $value): self { $this->ownerUrl = $value; return $this; }
    public function getSelectedDocuments(): array { return $this->selectedDocuments ?? []; }
    public function setSelectedDocuments(?array $value): self { $this->selectedDocuments = $value; return $this; }
    public function getLatencyMs(): ?int { return $this->latencyMs; }
    public function setLatencyMs(?int $value): self { $this->latencyMs = $value; return $this; }
    public function getInputTokens(): ?int { return $this->inputTokens; }
    public function setInputTokens(?int $value): self { $this->inputTokens = $value; return $this; }
    public function getOutputTokens(): ?int { return $this->outputTokens; }
    public function setOutputTokens(?int $value): self { $this->outputTokens = $value; return $this; }
    public function getErrorCode(): ?string { return $this->errorCode; }
    public function setErrorCode(?string $value): self { $this->errorCode = $value; return $this; }
    public function getRequestId(): ?string { return $this->requestId; }
    public function setRequestId(?string $value): self { $this->requestId = $value; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
