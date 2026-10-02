<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class LandingNarrativeSourceTest extends TestCase
{
    public function testLandingNarrativeSourceContainsElevenFrenchRows(): void
    {
        $path = dirname(__DIR__) . '/data/i18n/landing_narratives.fr.json';
        $rows = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(11, $rows);

        foreach ($rows as $row) {
            self::assertSame('fr', $row['locale']);
            self::assertNotEmpty($row['slug']);
            self::assertNotEmpty($row['narrative']);
        }
    }
}
