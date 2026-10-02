<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class HomePageSourceTest extends TestCase
{
    public function testHomePageSourceContainsFrenchHomePayload(): void
    {
        $path = dirname(__DIR__) . '/data/i18n/home_page.fr.json';
        $source = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('home', $source['slug']);
        self::assertSame('fr', $source['locale']);
        self::assertNotEmpty($source['homePage']['hero']['titleLines']);
        self::assertCount(4, $source['homePage']['practices']['cards']);
    }
}
