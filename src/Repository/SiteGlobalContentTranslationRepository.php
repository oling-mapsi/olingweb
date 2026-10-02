<?php

namespace App\Repository;

use App\Entity\SiteGlobalContentTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<SiteGlobalContentTranslation> */
class SiteGlobalContentTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SiteGlobalContentTranslation::class);
    }
}
