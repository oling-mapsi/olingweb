<?php

namespace App\Service;

use App\Entity\LocalizedSlugHistory;
use App\Entity\SitePage;
use App\Entity\SitePageTranslation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class SitePageTranslationSynchronizer
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslationSourceHasher $translationSourceHasher
    ) {
    }

    public function ensureFrenchTranslation(SitePage $page): SitePageTranslation
    {
        $translation = $page->getTranslation(SitePageTranslation::LOCALE_FR);
        if ($translation instanceof SitePageTranslation) {
            return $translation;
        }

        $translation = (new SitePageTranslation())
            ->setLocale(SitePageTranslation::LOCALE_FR)
            ->setSlug((string) $page->getSlug())
            ->setTitle((string) $page->getTitle())
            ->setSeoTitle($page->getTitle())
            ->setSeoDescription($page->getMetaDescription())
            ->setHeroBadge($page->getHeroBadge())
            ->setHeroTitle($page->getHeroTitle())
            ->setHeroIntro($page->getHeroIntro())
            ->setHeroSideHtml($page->getHeroSideHtml())
            ->setBodyHtml($page->getBodyHtml())
            ->setPublishedAt($page->getPublishedAt())
            ->setUnpublishedAt($page->getUnpublishedAt());

        $status = $page->getPublicationStatus();
        $translation->setTranslationStatus($status === null || $status === SitePageTranslation::STATUS_PUBLISHED ? SitePageTranslation::STATUS_PUBLISHED : SitePageTranslation::STATUS_DRAFT);
        $translation->setSourceContentHash($this->translationSourceHasher->hashSitePage($page));
        $translation->setSourceUpdatedAt(new \DateTimeImmutable());

        $page->addTranslation($translation);
        $this->entityManager->persist($translation);

        return $translation;
    }

    public function syncFrenchTranslationToLegacyFields(SitePage $page, ?User $changedBy = null): void
    {
        $translation = $this->ensureFrenchTranslation($page);
        $oldSlug = (string) $page->getSlug();
        $newSlug = $translation->getSlug();
        $wasPublished = $this->isLegacyPublished($page) || $translation->isPublished();

        $page
            ->setSlug($newSlug)
            ->setTitle($translation->getTitle())
            ->setMetaDescription($translation->getSeoDescription())
            ->setHeroBadge($translation->getHeroBadge())
            ->setHeroTitle($translation->getHeroTitle())
            ->setHeroIntro($translation->getHeroIntro())
            ->setHeroSideHtml($translation->getHeroSideHtml())
            ->setBodyHtml($translation->getBodyHtml())
            ->setPublishedAt($translation->getPublishedAt())
            ->setUnpublishedAt($translation->getUnpublishedAt());

        $page->setPublicationStatus($translation->getTranslationStatus() === SitePageTranslation::STATUS_PUBLISHED ? SitePageTranslation::STATUS_PUBLISHED : SitePageTranslation::STATUS_DRAFT);

        if ($oldSlug !== '' && $oldSlug !== $newSlug && $wasPublished && $page->getId() !== null) {
            $history = (new LocalizedSlugHistory())
                ->setResourceType(SitePage::class)
                ->setResourceId($page->getId())
                ->setLocale(SitePageTranslation::LOCALE_FR)
                ->setOldSlug($oldSlug)
                ->setNewSlug($newSlug)
                ->setChangedAt(new \DateTimeImmutable())
                ->setChangedBy($changedBy);

            $this->entityManager->persist($history);
        }

        $translation->setSourceContentHash($this->translationSourceHasher->hashSitePage($page));
        $translation->setSourceUpdatedAt(new \DateTimeImmutable());
    }

    private function isLegacyPublished(SitePage $page): bool
    {
        return $page->getPublicationStatus() === null || $page->getPublicationStatus() === SitePageTranslation::STATUS_PUBLISHED;
    }
}
