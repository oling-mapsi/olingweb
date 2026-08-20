<?php

namespace App\Entity;

use App\Repository\ChatLeadRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChatLeadRepository::class)]
#[ORM\Table(name: 'chat_lead')]
class ChatLead
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'lead')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ChatConversation $conversation = null;

    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 50)]
    private ?string $phone = null;

    #[ORM\Column(length: 255)]
    private ?string $company = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $needDescription = null;

    #[ORM\Column]
    private bool $rgpdConsent = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $rgpdConsentAt = null;

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

    public function setConversation(ChatConversation $conversation): self
    {
        $this->conversation = $conversation;
        if ($conversation->getLead() !== $this) {
            $conversation->setLead($this);
        }

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(string $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getNeedDescription(): ?string
    {
        return $this->needDescription;
    }

    public function setNeedDescription(string $needDescription): self
    {
        $this->needDescription = $needDescription;

        return $this;
    }

    public function isRgpdConsent(): bool
    {
        return $this->rgpdConsent;
    }

    public function setRgpdConsent(bool $rgpdConsent): self
    {
        $this->rgpdConsent = $rgpdConsent;

        return $this;
    }

    public function getRgpdConsentAt(): ?\DateTimeImmutable
    {
        return $this->rgpdConsentAt;
    }

    public function setRgpdConsentAt(\DateTimeImmutable $rgpdConsentAt): self
    {
        $this->rgpdConsentAt = $rgpdConsentAt;

        return $this;
    }

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
