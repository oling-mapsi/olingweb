<?php

namespace App\Tests;

use App\Entity\ChatPublicDocument;
use App\Repository\ChatPublicDocumentRepository;
use App\Service\Chat\ChatPublicContentIndexer;
use App\Service\Chat\ConfidentialProjectSanitizer;
use App\Service\Chat\PublicContentCatalog;
use PHPUnit\Framework\TestCase;

class PublicContentCatalogTest extends TestCase
{
    public function testFallsBackToSafeSnapshotWhenIndexTableIsUnavailable(): void
    {
        $repository = $this->createMock(ChatPublicDocumentRepository::class);
        $repository
            ->method('findActiveDocuments')
            ->willThrowException(new \RuntimeException('table missing'));

        $reference = (new ChatPublicDocument())
            ->setSourceType('reference')
            ->setSourceEntityId(1)
            ->setSafeTitle('Référence Eau et assainissement - AMOA progiciel')
            ->setSafeText('Mission AMOA progiciel en eau et assainissement avec cadrage, consultation, reprise de données et déploiement.')
            ->setUrl('/projets')
            ->setKeywords(['eau', 'eaux', 'assainissement', 'amoa', 'progiciel'])
            ->setSearchText('reference eau et assainissement amoa progiciel cadrage consultation reprise de donnees deploiement')
            ->setIsActive(true)
            ->setIsConfidentialReference(true)
            ->setChecksum('test')
            ->setUpdatedAt(new \DateTimeImmutable());

        $indexer = $this->createMock(ChatPublicContentIndexer::class);
        $indexer
            ->method('buildDocumentSnapshot')
            ->willReturn([$reference]);

        $catalog = new PublicContentCatalog($repository, $indexer);

        $documents = $catalog->findRelevantDocuments('avez vous des references eaux et assainissement', null, 2);

        self::assertCount(1, $documents);
        self::assertSame('reference', $documents[0]['type']);
        self::assertStringContainsString('Eau et assainissement', $documents[0]['title']);
    }

    public function testAccompaniedQuestionKeepsAnAnonymizedReferenceInSelection(): void
    {
        $documents = [];
        foreach (range(1, 4) as $index) {
            $documents[] = (new ChatPublicDocument())
                ->setSourceType('service')
                ->setSafeTitle('Cybersécurité et continuité '.$index)
                ->setSafeText('Sécurité, continuité, PCA et gouvernance pour le transport.')
                ->setUrl('/service-'.$index)
                ->setKeywords(['cyber', 'continuité', 'transport'])
                ->setSearchText('cybersecurite continuite pca transport')
                ->setIsActive(true)
                ->setChecksum('service-'.$index)
                ->setUpdatedAt(new \DateTimeImmutable());
        }
        $documents[] = (new ChatPublicDocument())
            ->setSourceType('reference')
            ->setSafeTitle('Référence Transport')
            ->setSafeText('Secteur Transport. Type d’organisation: grand port maritime. Mission de PCA et continuité.')
            ->setUrl('/projets')
            ->setKeywords(['transport', 'grand port maritime', 'pca', 'continuité', ConfidentialProjectSanitizer::SAFE_INDEX_MARKER])
            ->setSearchText('reference transport grand port maritime pca continuite')
            ->setIsActive(true)
            ->setIsConfidentialReference(true)
            ->setChecksum('reference-port')
            ->setUpdatedAt(new \DateTimeImmutable());

        $repository = $this->createMock(ChatPublicDocumentRepository::class);
        $repository->method('findActiveDocuments')->willReturn($documents);
        $catalog = new PublicContentCatalog($repository, $this->createMock(ChatPublicContentIndexer::class));

        $results = $catalog->findRelevantDocuments('Avez-vous accompagné des grands ports sur la continuité ou la cybersécurité ?', null, 4);

        self::assertContains('reference', array_column($results, 'type'));
        self::assertContains('/projets', array_column($results, 'url'));
    }
}
