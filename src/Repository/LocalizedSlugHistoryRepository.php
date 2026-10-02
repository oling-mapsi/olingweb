<?php

namespace App\Repository;

use App\Entity\LocalizedSlugHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LocalizedSlugHistory>
 */
class LocalizedSlugHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocalizedSlugHistory::class);
    }

    public function findOneByResourceLocaleAndOldSlug(string $resourceType, int $resourceId, string $locale, string $oldSlug): ?LocalizedSlugHistory
    {
        return $this->findOneBy([
            'resourceType' => $resourceType,
            'resourceId' => $resourceId,
            'locale' => $locale,
            'oldSlug' => $oldSlug,
        ]);
    }
}
