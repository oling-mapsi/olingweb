<?php

namespace App\Dto;

use App\Entity\SitePage;
use App\Entity\SitePageTranslation;

class SitePagePublicView
{
    public function __construct(
        private readonly SitePage $page,
        private readonly SitePageTranslation $translation
    ) {
    }

    public function getSourcePage(): SitePage
    {
        return $this->page;
    }

    public function getTranslation(): SitePageTranslation
    {
        return $this->translation;
    }

    public function getId(): ?int
    {
        return $this->page->getId();
    }

    public function getSlug(): string
    {
        return $this->translation->getSlug();
    }

    public function getTitle(): string
    {
        return $this->translation->getSeoTitle() ?: $this->translation->getTitle();
    }

    public function getMetaDescription(): ?string
    {
        return $this->translation->getSeoDescription();
    }

    public function getHeroBadge(): ?string
    {
        return $this->translation->getHeroBadge();
    }

    public function getHeroTitle(): ?string
    {
        return $this->translation->getHeroTitle();
    }

    public function getHeroIntro(): ?string
    {
        return $this->translation->getHeroIntro();
    }

    public function getHeroSideHtml(): ?string
    {
        return $this->translation->getHeroSideHtml();
    }

    public function getBodyHtml(): ?string
    {
        return $this->translation->getBodyHtml();
    }

    public function getHeroImage(): ?string
    {
        return $this->page->getHeroImage();
    }

    public function getCanonicalUrl(): ?string
    {
        return $this->page->getCanonicalUrl();
    }

    public function getCategories(): array
    {
        return $this->page->getCategories();
    }

    public function getTags(): array
    {
        return $this->page->getTags();
    }

    public function getPublicationDate(): ?\DateTimeImmutable
    {
        return $this->page->getPublicationDate();
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->translation->getPublishedAt() ?? $this->page->getPublishedAt();
    }
}
