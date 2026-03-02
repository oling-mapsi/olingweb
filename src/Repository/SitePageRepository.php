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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SitePage::class);
    }
}
