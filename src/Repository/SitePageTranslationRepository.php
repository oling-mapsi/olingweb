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

    public function findOnePublishedByLocaleAndSourceSlug(string $locale, string $sourceSlug): ?SitePageTranslation
    {
        return $this->createQueryBuilder('translation')
            ->join('translation.sitePage', 'page')
            ->andWhere('translation.locale = :locale')
            ->andWhere('translation.translationStatus = :status')
            ->andWhere('translation.unpublishedAt IS NULL')
            ->andWhere('page.slug = :sourceSlug')
            ->setParameter('locale', $locale)
            ->setParameter('status', SitePageTranslation::STATUS_PUBLISHED)
            ->setParameter('sourceSlug', $sourceSlug)
            ->getQuery()
            ->getOneOrNullResult();
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
