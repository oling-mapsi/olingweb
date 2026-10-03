<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

final class SeoQuickWinsSourceTest extends TestCase
{
    public function testRfeServiceNarrativeHasSeoSnippetAndOwnerLink(): void
    {
        $source = json_decode((string) file_get_contents(dirname(__DIR__).'/data/i18n/service_narratives.fr.json'), true, 512, JSON_THROW_ON_ERROR);
        $narrative = $source['narratives']['consulting/reforme-facturation-electronique-amoa'];

        self::assertNotEmpty($narrative['metaTitle'] ?? null);
        self::assertNotEmpty($narrative['metaDescription'] ?? null);
        self::assertContains('/facturation-electronique-amoa', array_column($narrative['supportLinks'], 'href'));
    }

    public function testSiFinanceLinksToRfeOwnerPage(): void
    {
        $source = json_decode((string) file_get_contents(dirname(__DIR__).'/data/i18n/landing_narratives.fr.json'), true, 512, JSON_THROW_ON_ERROR);
        $row = array_values(array_filter($source, static fn (array $row): bool => ($row['slug'] ?? null) === 'si-finance'))[0] ?? null;

        self::assertNotNull($row);
        self::assertContains('/facturation-electronique-amoa', array_column($row['narrative']['supportLinks'], 'href'));
    }
}
