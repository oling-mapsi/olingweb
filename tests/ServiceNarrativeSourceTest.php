<?php

namespace App\Tests;

use App\Entity\Services;
use App\Repository\LocalizedSlugHistoryRepository;
use App\Repository\SitePageTranslationRepository;
use App\Service\LocalizedContentResolver;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

class ServiceNarrativeSourceTest extends TestCase
{
    public function testFrenchSeoAiOwnerNarrativesAreExplicit(): void
    {
        $source = json_decode((string) file_get_contents(dirname(__DIR__) . '/data/i18n/service_narratives.fr.json'), true, 512, JSON_THROW_ON_ERROR);
        $narratives = $source['narratives'];

        $erp = json_encode($narratives['business-apps/erp'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        self::assertStringContainsString('AMOA ERP', $erp);
        self::assertStringContainsString('cabinet de conseil ERP', $erp);
        self::assertStringContainsString('cahier des charges', $erp);
        self::assertStringContainsString('/erp-progiciel', $erp);
        self::assertStringContainsString('/facturation-electronique-amoa', $erp);

        $rgpd = json_encode($narratives['expertises-audit/rgpd'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        self::assertStringContainsString('DPO externalisé', $rgpd);
        self::assertStringContainsString('AIPD', $rgpd);
        self::assertStringContainsString('/expertises/rgpd-dpo-gouvernance', $rgpd);

        $qse = json_encode($narratives['expertises-audit/qse'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        self::assertStringContainsString('Accompagnement ISO 9001', $qse);
        self::assertStringContainsString('revue de direction', $qse);
        self::assertStringContainsString('/conseil-qualite', $qse);
    }

    public function testServicePublicNarrativeComesFromServiceTranslation(): void
    {
        $service = new Services();
        $id = new \ReflectionProperty(Services::class, 'id');
        $id->setAccessible(true);
        $id->setValue($service, 123);

        $connection = $this->createMock(Connection::class);
        $connection->method('fetchAssociative')->willReturn([
            'designation' => 'Service DB',
            'slug' => 'service-db',
            'designation_short' => 'Service',
            'introduction_short' => 'Intro DB',
            'description' => 'Description DB',
            'description_short' => 'Description short DB',
            'public_narrative' => '{"headline":"Narrative DB","intro":"Intro narrative DB"}',
        ]);

        $resolver = new LocalizedContentResolver(
            $this->createMock(SitePageTranslationRepository::class),
            $this->createMock(LocalizedSlugHistoryRepository::class),
            $connection
        );

        $view = $resolver->getFrenchServiceView($service);

        self::assertSame('Narrative DB', $view->getPublicNarrative()['headline']);
        self::assertSame('Intro narrative DB', $view->getPublicNarrative()['intro']);
    }
}
