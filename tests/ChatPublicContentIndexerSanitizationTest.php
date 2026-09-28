<?php

namespace App\Tests;

use App\Entity\Metier;
use App\Entity\Projet;
use App\Repository\PracticeRepository;
use App\Repository\ProjetRepository;
use App\Repository\ServicesRepository;
use App\Repository\SitePageRepository;
use App\Repository\TeamRepository;
use App\Service\Chat\ChatPublicContentIndexer;
use App\Service\Chat\ConfidentialProjectSanitizer;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ChatPublicContentIndexerSanitizationTest extends TestCase
{
    public function testSafeProjectionRemovesClientIdentifiersAndKeepsSectorAndNeed(): void
    {
        $project = (new Projet())
            ->setDesignation('Mission confidentielle')
            ->setClientName('Compagnie Régionale des Services Numériques')
            ->setDescription('Pour Compagnie Régionale des Services Numériques, CRSN et Horizon Services : cadrage PCA et résilience.')
            ->setMetadata(['client_aliases' => ['Horizon Services']])
            ->setSoftwareRelation('PCA et résilience');
        $project->setMetier((new Metier())->setDesignation('Formation professionnelle'));

        $relatedProject = (new Projet())
            ->setDesignation('Autre mission confidentielle')
            ->setClientName('Horizon Portuaire Atlantique')
            ->setDescription('Mission distincte de continuité.');
        $relatedProject->setMetier((new Metier())->setDesignation('Transport'));

        $project->setDescription($project->getDescription().' HPA intervient aussi dans la description consolidée.');

        $projects = $this->createMock(ProjetRepository::class);
        $projects->method('findAll')->willReturn([$project, $relatedProject]);

        $indexer = new ChatPublicContentIndexer(
            $this->createMock(EntityManagerInterface::class),
            $this->emptyRepository(SitePageRepository::class),
            $this->emptyRepository(PracticeRepository::class),
            $this->emptyRepository(ServicesRepository::class),
            $projects,
            $this->emptyRepository(TeamRepository::class),
            $this->createMock(UrlGeneratorInterface::class),
            new ConfidentialProjectSanitizer(),
        );

        $documents = $indexer->buildDocumentSnapshot();

        self::assertCount(2, $documents);
        $document = $documents[0];
        foreach (['Compagnie Régionale', 'CRSN', 'Horizon Services', 'HPA'] as $identifier) {
            self::assertStringNotContainsStringIgnoringCase($identifier, $document->getSafeText());
            self::assertStringNotContainsStringIgnoringCase($identifier, $document->getSearchText());
        }
        self::assertStringContainsString('Formation professionnelle', $document->getSafeText());
        self::assertStringContainsString('PCA et résilience', $document->getSafeText());
        self::assertStringContainsString('formation professionnelle', $document->getSearchText());
        self::assertStringContainsString('pca et resilience', $document->getSearchText());
    }

    private function emptyRepository(string $class): object
    {
        $repository = $this->createMock($class);
        $repository->method('findAll')->willReturn([]);

        return $repository;
    }
}
