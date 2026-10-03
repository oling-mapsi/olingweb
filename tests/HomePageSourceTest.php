<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class HomePageSourceTest extends TestCase
{
    public function testHomePageSourcesContainCompleteLocalizedHomePayloads(): void
    {
        foreach (['fr', 'en', 'es'] as $locale) {
            $path = dirname(__DIR__) . sprintf('/data/i18n/home_page.%s.json', $locale);
            $source = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

            self::assertSame('home', $source['slug']);
            self::assertSame($locale, $source['locale']);
            self::assertNotEmpty($source['homePage']['hero']['titleLines']);
            self::assertCount(4, $source['homePage']['practices']['cards']);
            self::assertNotEmpty($source['homePage']['accompaniments']['title']);
            self::assertGreaterThan(0, count($source['homePage']['accompaniments']['items']));
            self::assertNotEmpty($source['homePage']['proof']['title']);
            self::assertGreaterThan(0, count($source['homePage']['proof']['items']));
            self::assertNotEmpty($source['homePage']['projects']['title']);
            self::assertNotEmpty($source['homePage']['resources']['title']);
            self::assertNotEmpty($source['homePage']['finalCta']['title']);
            self::assertNotEmpty($source['homePage']['finalCta']['text']);
        }
    }
}
