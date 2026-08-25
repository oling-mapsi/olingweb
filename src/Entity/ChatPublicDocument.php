<?php

namespace App\Entity;

use App\Repository\ChatPublicDocumentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChatPublicDocumentRepository::class)]
#[ORM\Table(name: 'chat_public_document')]
#[ORM\Index(columns: ['source_type', 'source_entity_id'], name: 'idx_chat_public_document_source')]
#[ORM\Index(columns: ['is_active', 'updated_at'], name: 'idx_chat_public_document_active')]
class ChatPublicDocument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    private string $sourceType = 'page';

    #[ORM\Column(nullable: true)]
    private ?int $sourceEntityId = null;

    #[ORM\Column(length: 255)]
    private string $safeTitle = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $safeText = '';

    #[ORM\Column(length: 255)]
    private string $url = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sectionTitle = null;

    #[ORM\Column(type: Types::JSON)]
    private array $keywords = [];

    #[ORM\Column(type: Types::TEXT)]
    private string $searchText = '';

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private bool $isConfidentialReference = false;

    #[ORM\Column(length: 64)]
    private string $checksum = '';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSourceType(): string
    {
        return $this->sourceType;
    }

    public function setSourceType(string $sourceType): self
    {
        $this->sourceType = $sourceType;

        return $this;
    }

    public function getSourceEntityId(): ?int
    {
        return $this->sourceEntityId;
    }

    public function setSourceEntityId(?int $sourceEntityId): self
    {
        $this->sourceEntityId = $sourceEntityId;

        return $this;
    }

    public function getSafeTitle(): string
    {
        return $this->safeTitle;
    }

    public function setSafeTitle(string $safeTitle): self
    {
        $this->safeTitle = $safeTitle;

        return $this;
    }

    public function getSafeText(): string
    {
        return $this->safeText;
    }

    public function setSafeText(string $safeText): self
    {
        $this->safeText = $safeText;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getSectionTitle(): ?string
    {
        return $this->sectionTitle;
    }

    public function setSectionTitle(?string $sectionTitle): self
    {
        $this->sectionTitle = $sectionTitle;

        return $this;
    }

    public function getKeywords(): array
    {
        return $this->keywords;
    }

    public function setKeywords(array $keywords): self
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function getSearchText(): string
    {
        return $this->searchText;
    }

    public function setSearchText(string $searchText): self
    {
        $this->searchText = $searchText;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function isConfidentialReference(): bool
    {
        return $this->isConfidentialReference;
    }

    public function setIsConfidentialReference(bool $isConfidentialReference): self
    {
        $this->isConfidentialReference = $isConfidentialReference;

        return $this;
    }

    public function getChecksum(): string
    {
        return $this->checksum;
    }

    public function setChecksum(string $checksum): self
    {
        $this->checksum = $checksum;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }
}
