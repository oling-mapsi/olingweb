<?php

namespace App\Repository;

use App\Entity\SitePage;
use App\Entity\SitePageRevision;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SitePageRevision>
 */
class SitePageRevisionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SitePageRevision::class);
    }

    public function nextRevisionNumber(SitePage $sitePage): int
    {
        if ($sitePage->getId() === null) {
            return 1;
        }

        $max = $this->createQueryBuilder('r')
            ->select('MAX(r.revisionNumber)')
            ->andWhere('r.sitePage = :sitePage')
            ->setParameter('sitePage', $sitePage)
            ->getQuery()
            ->getSingleScalarResult();

        return ((int) $max) + 1;
    }

    public function findLatestDraft(SitePage $sitePage): ?SitePageRevision
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.sitePage = :sitePage')
            ->andWhere('r.revisionState = :state')
            ->setParameter('sitePage', $sitePage)
            ->setParameter('state', 'draft')
            ->orderBy('r.revisionNumber', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLatestDraftForSlug(SitePage $sitePage, string $slug): ?SitePageRevision
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.sitePage = :sitePage')
            ->andWhere('r.revisionState = :state')
            ->andWhere('r.slug = :slug')
            ->setParameter('sitePage', $sitePage)
            ->setParameter('state', 'draft')
            ->setParameter('slug', $slug)
            ->orderBy('r.revisionNumber', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLatestDraftForSlugAndCanonicalFragment(SitePage $sitePage, string $slug, string $fragment): ?SitePageRevision
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.sitePage = :sitePage')
            ->andWhere('r.revisionState = :state')
            ->andWhere('r.slug = :slug')
            ->andWhere('r.canonicalUrl LIKE :fragment')
            ->setParameter('sitePage', $sitePage)
            ->setParameter('state', 'draft')
            ->setParameter('slug', $slug)
            ->setParameter('fragment', '%'.$fragment.'%')
            ->orderBy('r.revisionNumber', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPublishedRevisions(SitePage $sitePage, int $limit = 10): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.sitePage = :sitePage')
            ->andWhere('r.revisionState = :state')
            ->setParameter('sitePage', $sitePage)
            ->setParameter('state', 'published')
            ->orderBy('r.revisionNumber', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findPublishedRevisionsForSlug(SitePage $sitePage, string $slug, int $limit = 10): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.sitePage = :sitePage')
            ->andWhere('r.revisionState = :state')
            ->andWhere('r.slug = :slug')
            ->setParameter('sitePage', $sitePage)
            ->setParameter('state', 'published')
            ->setParameter('slug', $slug)
            ->orderBy('r.revisionNumber', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findPublishedRevisionsForSlugAndCanonicalFragment(SitePage $sitePage, string $slug, string $fragment, int $limit = 10): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.sitePage = :sitePage')
            ->andWhere('r.revisionState = :state')
            ->andWhere('r.slug = :slug')
            ->andWhere('r.canonicalUrl LIKE :fragment')
            ->setParameter('sitePage', $sitePage)
            ->setParameter('state', 'published')
            ->setParameter('slug', $slug)
            ->setParameter('fragment', '%'.$fragment.'%')
            ->orderBy('r.revisionNumber', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
