<?php

namespace App\Service;

use App\Entity\SitePage;
use App\Entity\SitePageTranslation;
use App\Repository\SitePageTranslationRepository;

class LocalizedContentResolver
{
    public function __construct(private readonly SitePageTranslationRepository $sitePageTranslationRepository)
    {
    }

    public function getTranslation(SitePage $page, string $locale): ?SitePageTranslation
    {
        SitePageTranslation::assertSupportedLocale($locale);

        return $page->getTranslation($locale) ?? $this->sitePageTranslationRepository->findOneByPageAndLocale($page, $locale);
    }

    public function getPublishedTranslation(SitePage $page, string $locale): ?SitePageTranslation
    {
        SitePageTranslation::assertSupportedLocale($locale);

        $translation = $page->getPublishedTranslation($locale);

        return $translation ?? $this->sitePageTranslationRepository->findOnePublishedByPageAndLocale($page, $locale);
    }
}
