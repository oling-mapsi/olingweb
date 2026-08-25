<?php

namespace App\Tests;

use App\Entity\ChatPublicDocument;
use App\Repository\ChatPublicDocumentRepository;
use App\Service\Chat\ChatPublicContentIndexer;
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
}
