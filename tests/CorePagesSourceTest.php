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
}
