<?php

namespace App\Repository;

use App\Entity\AnalyticsEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AnalyticsEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnalyticsEvent::class);
    }

    public function countEvents(string $eventName, \DateTimeImmutable $from, \DateTimeImmutable $to): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.eventName = :event')
            ->andWhere('e.occurredAt BETWEEN :from AND :to')
            ->setParameter('event', $eventName)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countDistinctSessions(\DateTimeImmutable $from, \DateTimeImmutable $to): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(DISTINCT e.sessionId)')
            ->andWhere('e.occurredAt BETWEEN :from AND :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param list<string> $eventNames
     */
    public function countEventsIn(array $eventNames, \DateTimeImmutable $from, \DateTimeImmutable $to): int
    {
        if ($eventNames === []) {
            return 0;
        }

        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.eventName IN (:events)')
            ->andWhere('e.occurredAt BETWEEN :from AND :to')
            ->setParameter('events', $eventNames)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array<int, array{date: string, visits: int}>
     */
    public function dailyVisits(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $rows = $connection->executeQuery(
            <<<'SQL'
                SELECT DATE(occurred_at) AS day, COUNT(DISTINCT session_id) AS visits
                FROM analytics_event
                WHERE event_name = :event AND occurred_at BETWEEN :from AND :to
                GROUP BY day
                ORDER BY day ASC
            SQL,
            [
                'event' => 'page_view',
                'from' => $from->format('Y-m-d H:i:s'),
                'to' => $to->format('Y-m-d H:i:s'),
            ]
        )->fetchAllAssociative();

        return array_map(static fn (array $row): array => [
            'date' => (string) $row['day'],
            'visits' => (int) $row['visits'],
        ], $rows);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function topPages(\DateTimeImmutable $from, \DateTimeImmutable $to, int $limit = 25): array
    {
        return $this->createQueryBuilder('e')
            ->select('e.pagePath AS page, COUNT(e.id) AS views, COUNT(DISTINCT e.sessionId) AS sessions')
            ->andWhere('e.eventName = :event')
            ->andWhere('e.occurredAt BETWEEN :from AND :to')
            ->groupBy('e.pagePath')
            ->orderBy('views', 'DESC')
            ->addOrderBy('sessions', 'DESC')
            ->setMaxResults($limit)
            ->setParameter('event', 'page_view')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function sources(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return $this->createQueryBuilder('e')
            ->select('e.source AS source, COUNT(DISTINCT e.sessionId) AS sessions, COUNT(e.id) AS events')
            ->andWhere('e.occurredAt BETWEEN :from AND :to')
            ->groupBy('e.source')
            ->orderBy('sessions', 'DESC')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function transitions(\DateTimeImmutable $from, \DateTimeImmutable $to, int $limit = 25): array
    {
        return $this->createQueryBuilder('e')
            ->select('e.previousPagePath AS previousPage, e.pagePath AS page, COUNT(e.id) AS count')
            ->andWhere('e.previousPagePath IS NOT NULL')
            ->andWhere('e.previousPagePath != e.pagePath')
            ->andWhere('e.occurredAt BETWEEN :from AND :to')
            ->groupBy('e.previousPagePath, e.pagePath')
            ->orderBy('count', 'DESC')
            ->setMaxResults($limit)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return array<int, AnalyticsEvent>
     */
    public function sessionEvents(string $sessionId, \DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.sessionId = :session')
            ->andWhere('e.occurredAt BETWEEN :from AND :to')
            ->orderBy('e.occurredAt', 'ASC')
            ->setParameter('session', $sessionId)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getResult();
    }
}
