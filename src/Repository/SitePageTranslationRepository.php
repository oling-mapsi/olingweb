<?php

namespace App\Repository;

use App\Entity\SitePage;
use App\Entity\SitePageTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SitePageTranslation>
 */
class SitePageTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SitePageTranslation::class);
    }

    public function findOneByPageAndLocale(SitePage $page, string $locale): ?SitePageTranslation
    {
        return $this->findOneBy(['sitePage' => $page, 'locale' => $locale]);
    }

    public function findOnePublishedByPageAndLocale(SitePage $page, string $locale): ?SitePageTranslation
    {
        return $this->findOneBy([
            'sitePage' => $page,
            'locale' => $locale,
            'translationStatus' => SitePageTranslation::STATUS_PUBLISHED,
            'unpublishedAt' => null,
        ]);
    }

    public function findOnePublishedByLocaleAndSlug(string $locale, string $slug): ?SitePageTranslation
    {
        return $this->findOneBy([
            'locale' => $locale,
            'slug' => $slug,
            'translationStatus' => SitePageTranslation::STATUS_PUBLISHED,
            'unpublishedAt' => null,
        ]);
    }

    /**
     * @return SitePageTranslation[]
     */
    public function findPublishedByLocale(string $locale): array
    {
        return $this->findBy([
            'locale' => $locale,
            'translationStatus' => SitePageTranslation::STATUS_PUBLISHED,
            'unpublishedAt' => null,
        ]);
    }
}
