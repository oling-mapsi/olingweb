<?php

namespace App\Repository;

use App\Entity\SitePage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SitePage>
 *
 * @method SitePage|null find($id, $lockMode = null, $lockVersion = null)
 * @method SitePage|null findOneBy(array $criteria, array $orderBy = null)
 * @method SitePage[]    findAll()
 * @method SitePage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SitePageRepository extends ServiceEntityRepository
{
    private const RESOURCE_INDEX_SLUG = 'ressources';
    private const RESOURCE_ARTICLE_PREFIX = 'ressource-';
    private const PUBLISHED_STATUS = 'published';
    private const BLOCKED_RESOURCE_PUBLIC_SLUG_PREFIXES = [
        'pilot-',
        'test-',
        'demo-',
        'e2e-',
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SitePage::class);
    }

    public function findResourceIndexPage(): ?SitePage
    {
        return $this->findOneBy(['slug' => self::RESOURCE_INDEX_SLUG]);
    }

    /**
     * @return SitePage[]
     */
    public function findResourceArticles(): array
    {
        $pages = $this->createQueryBuilder('p')
            ->andWhere('p.slug LIKE :prefix')
            ->andWhere('p.publicationStatus = :status')
            ->setParameter('prefix', self::RESOURCE_ARTICLE_PREFIX . '%')
            ->setParameter('status', self::PUBLISHED_STATUS)
            ->orderBy('p.publicationDate', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();

        return array_values(array_filter($pages, fn (SitePage $page): bool => !$this->isBlockedResourceArticle($page)));
    }

    public function findResourceArticleByPublicSlug(string $publicSlug): ?SitePage
    {
        if ($this->isBlockedResourcePublicSlug($publicSlug)) {
            return null;
        }

        return $this->findOneBy([
            'slug' => self::RESOURCE_ARTICLE_PREFIX . $publicSlug,
            'publicationStatus' => self::PUBLISHED_STATUS,
        ]);
    }

    public function findOneByExternalId(string $externalId): ?SitePage
    {
        return $this->findOneBy(['externalId' => $externalId]);
    }

    /**
     * @return SitePage[]
     */
    public function findRelatedResourceArticles(string $excludedStoredSlug, int $limit = 4): array
    {
        $pages = $this->createQueryBuilder('p')
            ->andWhere('p.slug LIKE :prefix')
            ->andWhere('p.slug != :excluded')
            ->andWhere('p.publicationStatus = :status')
            ->setParameter('prefix', self::RESOURCE_ARTICLE_PREFIX . '%')
            ->setParameter('excluded', $excludedStoredSlug)
            ->setParameter('status', self::PUBLISHED_STATUS)
            ->orderBy('p.publicationDate', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();

        $pages = array_values(array_filter($pages, fn (SitePage $page): bool => !$this->isBlockedResourceArticle($page)));

        return array_slice($pages, 0, $limit);
    }

    private function isBlockedResourceArticle(SitePage $page): bool
    {
        $slug = (string) $page->getSlug();
        if (!str_starts_with($slug, self::RESOURCE_ARTICLE_PREFIX)) {
            return false;
        }

        $publicSlug = substr($slug, strlen(self::RESOURCE_ARTICLE_PREFIX));

        return $publicSlug === false || $this->isBlockedResourcePublicSlug($publicSlug);
    }

    private function isBlockedResourcePublicSlug(string $publicSlug): bool
    {
        foreach (self::BLOCKED_RESOURCE_PUBLIC_SLUG_PREFIXES as $prefix) {
            if (str_starts_with($publicSlug, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
