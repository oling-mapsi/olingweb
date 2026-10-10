<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class CorePagesSourceTest extends TestCase
{
    public function testCorePagesSourceContainsSevenFrenchPages(): void
    {
        $source = json_decode((string) file_get_contents(dirname(__DIR__) . '/data/i18n/core_pages.fr.json'), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('fr', $source['locale']);
        self::assertCount(8, $source['pages']);
        self::assertArrayHasKey('contact', $source['pages']);
        self::assertArrayHasKey('services', $source['pages']);
        self::assertArrayHasKey('rse', $source['pages']);
    }

    public function testRsePageHasSeoAndHeroContent(): void
    {
        $source = json_decode((string) file_get_contents(dirname(__DIR__) . '/data/i18n/core_pages.fr.json'), true, 512, JSON_THROW_ON_ERROR);
        $page = $source['pages']['rse'];

        foreach (['seoTitle', 'metaDescription', 'title', 'intro'] as $field) {
            self::assertNotEmpty($page[$field] ?? null, sprintf('RSE %s must not be empty.', $field));
        }
    }

    public function testPracticeNarrativesHaveSeoAndHeroContent(): void
    {
        $source = json_decode((string) file_get_contents(dirname(__DIR__) . '/data/i18n/practice_narratives.fr.json'), true, 512, JSON_THROW_ON_ERROR);

        foreach (['business-apps', 'expertises-audit', 'mapsi'] as $slug) {
            $narrative = $source['narratives'][$slug] ?? [];
            foreach (['headline', 'metaTitle', 'metaDescription', 'intro'] as $field) {
                self::assertNotEmpty($narrative[$field] ?? null, sprintf('%s %s must not be empty.', $slug, $field));
            }
        }
    }

    public function testMapsiPracticeLinksToInternalMapsiServicePages(): void
    {
        $source = json_decode((string) file_get_contents(dirname(__DIR__) . '/data/i18n/practice_narratives.fr.json'), true, 512, JSON_THROW_ON_ERROR);
        $hrefs = array_column($source['narratives']['mapsi']['supportLinks'], 'href');

        self::assertContains('/mapsi/automatisation-et-workflow', $hrefs);
        self::assertContains('/mapsi/mapsi-audit', $hrefs);
        self::assertContains('/mapsi/mapsi-risques', $hrefs);
        self::assertContains('/mapsi/mapsi-secu', $hrefs);
    }

    public function testHomePageLinksToSeoAiOwnerPages(): void
    {
        $source = json_decode((string) file_get_contents(dirname(__DIR__) . '/data/i18n/home_page.fr.json'), true, 512, JSON_THROW_ON_ERROR);
        $urls = array_column($source['homePage']['practices']['cards'], 'url');

        self::assertContains('/amoa-si', $urls);
        self::assertContains('/business-apps/erp', $urls);
        self::assertContains('/expertises-audit/rgpd', $urls);
        self::assertContains('/cyber-securite', $urls);
    }

    public function testServiceAndExpertiseIndexesExposeSpecializedIndexablePages(): void
    {
        $services = (string) file_get_contents(dirname(__DIR__) . '/templates/services-index.html.twig');
        $expertises = (string) file_get_contents(dirname(__DIR__) . '/templates/expertises/index.html.twig');

        foreach ([
            '/business-apps/systeme-dinformation-geographique',
            '/business-apps/ms365',
            '/business-apps/developpements-specifiques',
            '/consulting/tech-refresh',
        ] as $href) {
            self::assertStringContainsString($href, $services);
        }

        foreach ([
            '/expertises-audit/achats-marches',
            '/expertises-audit/controle-interne',
            '/expertises-audit/controle-de-gestion',
            '/expertises-audit/finance',
            '/expertises-audit/rse',
        ] as $href) {
            self::assertStringContainsString($href, $expertises);
        }
    }
}
