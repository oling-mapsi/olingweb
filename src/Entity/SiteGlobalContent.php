<?php

namespace App\Entity;

use App\Repository\SiteGlobalContentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SiteGlobalContentRepository::class)]
#[ORM\Table(name: 'site_global_content')]
#[ORM\HasLifecycleCallbacks]
class SiteGlobalContent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64, unique: true)]
    private string $identifier = '';

    #[ORM\Column]
    private bool $enabled = true;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $technicalConfig = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt ??= $now; }
    #[ORM\PreUpdate]
    public function onPreUpdate(): void { $this->updatedAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getIdentifier(): string { return $this->identifier; }
    public function setIdentifier(string $identifier): self { $this->identifier = $identifier; return $this; }
    public function isEnabled(): bool { return $this->enabled; }
    public function setEnabled(bool $enabled): self { $this->enabled = $enabled; return $this; }
    public function getTechnicalConfig(): ?array { return $this->technicalConfig; }
    public function setTechnicalConfig(?array $technicalConfig): self { $this->technicalConfig = $technicalConfig; return $this; }
}
