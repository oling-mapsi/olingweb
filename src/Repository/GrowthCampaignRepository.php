<?php

namespace App\Repository;

use App\Entity\GrowthCampaign;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<GrowthCampaign> */
class GrowthCampaignRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, GrowthCampaign::class); }

    /** @return GrowthCampaign[] */
    public function findRecent(int $limit = 50): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
