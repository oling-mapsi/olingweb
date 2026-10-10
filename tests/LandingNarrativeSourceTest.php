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

    public function testCyberLandingNarrativeIsIso27001Owner(): void
    {
        $path = dirname(__DIR__) . '/data/i18n/landing_narratives.fr.json';
        $rows = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $bySlug = array_column($rows, null, 'slug');
        $cyber = json_encode($bySlug['cyber-securite']['narrative'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        self::assertStringContainsString('ISO 27001', $cyber);
        self::assertStringContainsString('ISO 27001:2022', $cyber);
        self::assertStringContainsString('/expertises-audit/rgpd', $cyber);
    }
}
