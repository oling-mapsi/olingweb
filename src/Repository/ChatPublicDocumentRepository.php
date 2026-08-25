<?php

namespace App\Repository;

use App\Entity\ChatPublicDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChatPublicDocument>
 */
class ChatPublicDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatPublicDocument::class);
    }

    /**
     * @return ChatPublicDocument[]
     */
    public function findActiveDocuments(): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('d.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
