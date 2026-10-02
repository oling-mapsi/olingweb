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
