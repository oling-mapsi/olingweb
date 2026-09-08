<?php

namespace App\Repository;

use App\Entity\ErpQuestionnaireSubmission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ErpQuestionnaireSubmission>
 */
class ErpQuestionnaireSubmissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ErpQuestionnaireSubmission::class);
    }

    public function findOneByPublicToken(string $token): ?ErpQuestionnaireSubmission
    {
        return $this->findOneBy(['publicToken' => $token]);
    }
}
