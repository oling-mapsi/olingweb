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
}
