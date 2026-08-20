<?php

namespace App\Repository;

use App\Entity\ChatConversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChatConversation>
 */
class ChatConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatConversation::class);
    }

    public function findOneByPublicToken(string $token): ?ChatConversation
    {
        return $this->findOneBy(['publicToken' => $token]);
    }

    /**
     * @return ChatConversation[]
     */
    public function findForAdminList(int $limit = 100): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.lead', 'l')
            ->addSelect('l')
            ->orderBy('c.lastMessageAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countByStatus(string $status): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countOpenConversations(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.status IN (:statuses)')
            ->setParameter('statuses', [ChatConversation::STATUS_ACTIVE, ChatConversation::STATUS_LEAD_PENDING])
            ->getQuery()
            ->getSingleScalarResult();
    }
}
