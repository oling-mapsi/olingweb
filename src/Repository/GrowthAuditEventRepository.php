<?php

namespace App\Repository;

use App\Entity\GrowthAuditEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<GrowthAuditEvent> */
class GrowthAuditEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, GrowthAuditEvent::class); }
}
