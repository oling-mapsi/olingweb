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
}
