<?php

namespace App\Entity;

use App\Repository\HomeSectionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HomeSectionRepository::class)]
class HomeSection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 190, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $eyebrow = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $intro = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ctaLabel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ctaUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ctaLabelSecondary = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ctaUrlSecondary = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getEyebrow(): ?string
    {
        return $this->eyebrow;
    }

    public function setEyebrow(?string $eyebrow): self
    {
        $this->eyebrow = $eyebrow;

        return $this;
    }

    public function getIntro(): ?string
    {
        return $this->intro;
    }

    public function setIntro(?string $intro): self
    {
        $this->intro = $intro;

        return $this;
    }

    public function getCtaLabel(): ?string
    {
        return $this->ctaLabel;
    }

    public function setCtaLabel(?string $ctaLabel): self
    {
        $this->ctaLabel = $ctaLabel;

        return $this;
    }

    public function getCtaUrl(): ?string
    {
        return $this->ctaUrl;
    }

    public function setCtaUrl(?string $ctaUrl): self
    {
        $this->ctaUrl = $ctaUrl;

        return $this;
    }

    public function getCtaLabelSecondary(): ?string
    {
        return $this->ctaLabelSecondary;
    }

    public function setCtaLabelSecondary(?string $ctaLabelSecondary): self
    {
        $this->ctaLabelSecondary = $ctaLabelSecondary;

        return $this;
    }

    public function getCtaUrlSecondary(): ?string
    {
        return $this->ctaUrlSecondary;
    }

    public function setCtaUrlSecondary(?string $ctaUrlSecondary): self
    {
        $this->ctaUrlSecondary = $ctaUrlSecondary;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function touchUpdatedAt(): self
    {
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
