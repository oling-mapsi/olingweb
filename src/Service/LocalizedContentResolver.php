<?php

namespace App\Service;

use App\Dto\SitePagePublicView;
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

    public function getFrenchPublicView(SitePage $page): SitePagePublicView
    {
        $translation = $this->getTranslation($page, SitePageTranslation::LOCALE_FR);
        if (!$translation instanceof SitePageTranslation) {
            throw new \LogicException(sprintf('Missing FR translation for SitePage #%s.', $page->getId() ?? 'new'));
        }

        return new SitePagePublicView($page, $translation);
    }
}
