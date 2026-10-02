<?php

namespace App\Repository;

use App\Entity\RelatedLinkTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<RelatedLinkTranslation> */
class RelatedLinkTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RelatedLinkTranslation::class);
    }
}
