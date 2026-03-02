<?php

namespace App\Repository;

use App\Entity\HomeProjectKpi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HomeProjectKpi>
 *
 * @method HomeProjectKpi|null find($id, $lockMode = null, $lockVersion = null)
 * @method HomeProjectKpi|null findOneBy(array $criteria, array $orderBy = null)
 * @method HomeProjectKpi[]    findAll()
 * @method HomeProjectKpi[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HomeProjectKpiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HomeProjectKpi::class);
    }
}
