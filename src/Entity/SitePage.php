<?php

namespace App\Entity;

use App\Repository\SitePageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SitePageRepository::class)]
#[ORM\Table(name: 'site_page')]
class SitePage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $metaDescription = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $heroBadge = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $heroTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $heroIntro = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $heroSideHtml = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bodyHtml = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $heroImage = null;

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

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getHeroBadge(): ?string
    {
        return $this->heroBadge;
    }

    public function setHeroBadge(?string $heroBadge): self
    {
        $this->heroBadge = $heroBadge;

        return $this;
    }

    public function getHeroTitle(): ?string
    {
        return $this->heroTitle;
    }

    public function setHeroTitle(?string $heroTitle): self
    {
        $this->heroTitle = $heroTitle;

        return $this;
    }

    public function getHeroIntro(): ?string
    {
        return $this->heroIntro;
    }

    public function setHeroIntro(?string $heroIntro): self
    {
        $this->heroIntro = $heroIntro;

        return $this;
    }

    public function getHeroSideHtml(): ?string
    {
        return $this->heroSideHtml;
    }

    public function setHeroSideHtml(?string $heroSideHtml): self
    {
        $this->heroSideHtml = $heroSideHtml;

        return $this;
    }

    public function getBodyHtml(): ?string
    {
        return $this->bodyHtml;
    }

    public function setBodyHtml(?string $bodyHtml): self
    {
        $this->bodyHtml = $bodyHtml;

        return $this;
    }

    public function getHeroImage(): ?string
    {
        return $this->heroImage;
    }

    public function setHeroImage(?string $heroImage): self
    {
        $this->heroImage = $heroImage;

        return $this;
    }
}
