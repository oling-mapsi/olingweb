<?php

namespace App\Tests;

use App\Service\SeoStructuredContentAdminMapper;
use PHPUnit\Framework\TestCase;

final class SeoStructuredContentAdminMapperTest extends TestCase
{
    public function testLandingNarrativeMergePreservesUnknownAndSiblingKeys(): void
    {
        $mapper = new SeoStructuredContentAdminMapper();

        $merged = $mapper->mergeLandingNarrative([
            'homePage' => ['title' => 'Accueil'],
            'landingNarrative' => [
                'metaTitle' => 'Ancien title',
                'unknownBlock' => ['kept' => true],
                'missionPhases' => [['title' => 'Cadrage']],
            ],
            'corePage' => ['title' => 'Core'],
        ], [
            'metaTitle' => 'Nouveau title',
            'metaDescription' => 'Nouvelle description',
            'heroTitle' => 'Nouveau H1',
            'heroIntro' => 'Intro',
            'promise' => 'Promesse',
            'focus' => "ERP\nAMOA",
            'citablePassages' => "Citation | Texte cite",
            'supportLinks' => "ERP | /erp-progiciel | Hub ERP",
        ]);

        self::assertSame(['title' => 'Accueil'], $merged['homePage']);
        self::assertSame(['title' => 'Core'], $merged['corePage']);
        self::assertSame(['kept' => true], $merged['landingNarrative']['unknownBlock']);
        self::assertSame([['title' => 'Cadrage']], $merged['landingNarrative']['missionPhases']);
        self::assertSame('Nouveau title', $merged['landingNarrative']['metaTitle']);
        self::assertSame(['ERP', 'AMOA'], $merged['landingNarrative']['focus']);
        self::assertSame([['title' => 'Citation', 'text' => 'Texte cite']], $merged['landingNarrative']['citablePassages']);
        self::assertSame([['label' => 'ERP', 'href' => '/erp-progiciel', 'description' => 'Hub ERP']], $merged['landingNarrative']['supportLinks']);
    }

    public function testServiceNarrativeMergePreservesNonExposedFields(): void
    {
        $mapper = new SeoStructuredContentAdminMapper();

        $merged = $mapper->mergeServiceNarrative([
            'headline' => 'Ancien H1',
            'citablePassages' => [['title' => 'Conserve', 'text' => 'Texte']],
            'supportLinks' => [['label' => 'RGPD', 'href' => '/expertises-audit/rgpd']],
        ], [
            'metaTitle' => 'Nouveau title',
            'metaDescription' => 'Nouvelle description',
            'eyebrow' => 'AMOA ERP',
            'headline' => 'Nouveau H1',
            'intro' => 'Intro',
            'promise' => 'Promesse',
        ]);

        self::assertSame('Nouveau H1', $merged['headline']);
        self::assertSame('Nouveau title', $merged['metaTitle']);
        self::assertSame([['title' => 'Conserve', 'text' => 'Texte']], $merged['citablePassages']);
        self::assertSame([['label' => 'RGPD', 'href' => '/expertises-audit/rgpd']], $merged['supportLinks']);
    }
}
