<?php

namespace App\Repository;

use App\Entity\AnalyticsConversionAttribution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AnalyticsConversionAttributionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnalyticsConversionAttribution::class);
    }

    public function countBetween(\DateTimeImmutable $from, \DateTimeImmutable $to): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.convertedAt BETWEEN :from AND :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array<int, array{entryPage: string, source: string, conversions: int}>
     */
    public function byEntryAndSource(\DateTimeImmutable $from, \DateTimeImmutable $to, int $limit = 20): array
    {
        $rows = $this->createQueryBuilder('a')
            ->select('a.entryPage AS entryPage, a.source AS source, COUNT(a.id) AS conversions')
            ->andWhere('a.convertedAt BETWEEN :from AND :to')
            ->groupBy('a.entryPage, a.source')
            ->orderBy('conversions', 'DESC')
            ->addOrderBy('a.entryPage', 'ASC')
            ->setMaxResults($limit)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getArrayResult();

        return array_map(static fn (array $row): array => [
            'entryPage' => (string) $row['entryPage'],
            'source' => (string) $row['source'],
            'conversions' => (int) $row['conversions'],
        ], $rows);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function recent(\DateTimeImmutable $from, \DateTimeImmutable $to, int $limit = 20): array
    {
        return $this->createQueryBuilder('a')
            ->select('a.conversionType AS type, a.entryPage AS entryPage, a.conversionPage AS conversionPage, a.source AS source, a.visitedPages AS visitedPages, a.convertedAt AS convertedAt')
            ->andWhere('a.convertedAt BETWEEN :from AND :to')
            ->orderBy('a.convertedAt', 'DESC')
            ->setMaxResults($limit)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getArrayResult();
    }
}
