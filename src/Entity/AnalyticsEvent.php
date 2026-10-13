<?php

namespace App\Entity;

use App\Repository\AnalyticsEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnalyticsEventRepository::class)]
#[ORM\Table(name: 'analytics_event')]
#[ORM\Index(columns: ['occurred_at'], name: 'idx_analytics_event_occurred_at')]
#[ORM\Index(columns: ['session_id', 'occurred_at'], name: 'idx_analytics_event_session_time')]
#[ORM\Index(columns: ['event_name', 'occurred_at'], name: 'idx_analytics_event_name_time')]
#[ORM\Index(columns: ['page_path', 'occurred_at'], name: 'idx_analytics_event_page_time')]
#[ORM\UniqueConstraint(name: 'uniq_analytics_event_event_id', columns: ['event_id'])]
class AnalyticsEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    private string $eventId;

    #[ORM\Column(length: 64)]
    private string $sessionId;

    #[ORM\Column(length: 64)]
    private string $eventName;

    #[ORM\Column(length: 255)]
    private string $pagePath;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $previousPagePath = null;

    #[ORM\Column(length: 64)]
    private string $source = 'unknown';

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $utmSource = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $utmMedium = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $utmCampaign = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $locale = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $occurredAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->occurredAt = $now;
        $this->createdAt = $now;
    }

    public function getId(): ?int { return $this->id; }
    public function getEventId(): string { return $this->eventId; }
    public function setEventId(string $eventId): self { $this->eventId = $eventId; return $this; }
    public function getSessionId(): string { return $this->sessionId; }
    public function setSessionId(string $sessionId): self { $this->sessionId = $sessionId; return $this; }
    public function getEventName(): string { return $this->eventName; }
    public function setEventName(string $eventName): self { $this->eventName = $eventName; return $this; }
    public function getPagePath(): string { return $this->pagePath; }
    public function setPagePath(string $pagePath): self { $this->pagePath = $pagePath; return $this; }
    public function getPreviousPagePath(): ?string { return $this->previousPagePath; }
    public function setPreviousPagePath(?string $previousPagePath): self { $this->previousPagePath = $previousPagePath; return $this; }
    public function getSource(): string { return $this->source; }
    public function setSource(string $source): self { $this->source = $source; return $this; }
    public function getUtmSource(): ?string { return $this->utmSource; }
    public function setUtmSource(?string $utmSource): self { $this->utmSource = $utmSource; return $this; }
    public function getUtmMedium(): ?string { return $this->utmMedium; }
    public function setUtmMedium(?string $utmMedium): self { $this->utmMedium = $utmMedium; return $this; }
    public function getUtmCampaign(): ?string { return $this->utmCampaign; }
    public function setUtmCampaign(?string $utmCampaign): self { $this->utmCampaign = $utmCampaign; return $this; }
    public function getLocale(): ?string { return $this->locale; }
    public function setLocale(?string $locale): self { $this->locale = $locale; return $this; }
    public function getOccurredAt(): \DateTimeImmutable { return $this->occurredAt; }
    public function setOccurredAt(\DateTimeImmutable $occurredAt): self { $this->occurredAt = $occurredAt; return $this; }
}
