<?php

namespace App\Entity;

use App\Repository\AnalyticsConversionAttributionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnalyticsConversionAttributionRepository::class)]
#[ORM\Table(name: 'analytics_conversion_attribution')]
#[ORM\Index(columns: ['converted_at'], name: 'idx_analytics_conversion_converted_at')]
#[ORM\Index(columns: ['session_id'], name: 'idx_analytics_conversion_session')]
#[ORM\Index(columns: ['entry_page', 'source'], name: 'idx_analytics_conversion_entry_source')]
#[ORM\UniqueConstraint(name: 'uniq_analytics_conversion_reference', columns: ['conversion_type', 'conversion_reference'])]
class AnalyticsConversionAttribution
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    private string $conversionType;

    #[ORM\Column(length: 80)]
    private string $conversionReference;

    #[ORM\Column(length: 64)]
    private string $sessionId;

    #[ORM\Column(length: 255)]
    private string $entryPage;

    #[ORM\Column(length: 255)]
    private string $conversionPage;

    #[ORM\Column(length: 64)]
    private string $source;

    /** @var string[] */
    #[ORM\Column(type: Types::JSON)]
    private array $visitedPages = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $convertedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->convertedAt = $now;
        $this->createdAt = $now;
    }

    public function getId(): ?int { return $this->id; }
    public function getConversionType(): string { return $this->conversionType; }
    public function setConversionType(string $conversionType): self { $this->conversionType = $conversionType; return $this; }
    public function getConversionReference(): string { return $this->conversionReference; }
    public function setConversionReference(string $conversionReference): self { $this->conversionReference = $conversionReference; return $this; }
    public function getSessionId(): string { return $this->sessionId; }
    public function setSessionId(string $sessionId): self { $this->sessionId = $sessionId; return $this; }
    public function getEntryPage(): string { return $this->entryPage; }
    public function setEntryPage(string $entryPage): self { $this->entryPage = $entryPage; return $this; }
    public function getConversionPage(): string { return $this->conversionPage; }
    public function setConversionPage(string $conversionPage): self { $this->conversionPage = $conversionPage; return $this; }
    public function getSource(): string { return $this->source; }
    public function setSource(string $source): self { $this->source = $source; return $this; }
    /** @return string[] */
    public function getVisitedPages(): array { return $this->visitedPages; }
    /** @param string[] $visitedPages */
    public function setVisitedPages(array $visitedPages): self { $this->visitedPages = array_values($visitedPages); return $this; }
    public function getConvertedAt(): \DateTimeImmutable { return $this->convertedAt; }
    public function setConvertedAt(\DateTimeImmutable $convertedAt): self { $this->convertedAt = $convertedAt; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
