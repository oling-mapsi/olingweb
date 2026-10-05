<?php

namespace App\Entity;

use App\Repository\GrowthAuditEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GrowthAuditEventRepository::class)]
#[ORM\Table(name: 'growth_audit_event')]
class GrowthAuditEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: GrowthCampaign::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?GrowthCampaign $campaign = null;

    #[ORM\Column(length: 80)]
    private string $eventName = '';

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $actor = null;

    #[ORM\Column(type: Types::JSON)]
    private array $context = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getCampaign(): ?GrowthCampaign { return $this->campaign; }
    public function setCampaign(?GrowthCampaign $campaign): self { $this->campaign = $campaign; return $this; }
    public function getEventName(): string { return $this->eventName; }
    public function setEventName(string $eventName): self { $this->eventName = $eventName; return $this; }
    public function getActor(): ?string { return $this->actor; }
    public function setActor(?string $actor): self { $this->actor = $actor; return $this; }
    public function getContext(): array { return $this->context; }
    public function setContext(array $context): self { $this->context = $context; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
