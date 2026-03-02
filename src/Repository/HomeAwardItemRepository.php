<?php

namespace App\Repository;

use App\Entity\HomeAwardItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HomeAwardItem>
 *
 * @method HomeAwardItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method HomeAwardItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method HomeAwardItem[]    findAll()
 * @method HomeAwardItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HomeAwardItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HomeAwardItem::class);
    }
}
