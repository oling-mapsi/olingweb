<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

final class LlmsTxtTest extends TestCase
{
    public function testLlmsTxtIsPublicAndScopedToPublishedContent(): void
    {
        $path = dirname(__DIR__).'/public/llms.txt';

        self::assertFileExists($path);
        $content = (string) file_get_contents($path);

        self::assertStringContainsString('# OLING Management & Technologie', $content);
        self::assertStringContainsString('## Main French URLs', $content);
        self::assertStringContainsString('## Main English URLs', $content);
        self::assertStringContainsString('## Main Spanish URLs', $content);
        self::assertStringContainsString('https://oling.fr/sitemap.xml', $content);

        self::assertDoesNotMatchRegularExpression('/\\b(admin|intranet|back[- ]?office|draft|staging|localhost)\\b/i', $content);
        self::assertDoesNotMatchRegularExpression('/https?:\\/\\/(?!oling\\.fr\\/)/i', $content);
    }

    public function testLlmsTxtDoesNotExposeBrokenMarkdownLinks(): void
    {
        $content = (string) file_get_contents(dirname(__DIR__).'/public/llms.txt');
        preg_match_all('/https:\\/\\/oling\\.fr\\/[^\\s)]+/', $content, $matches);

        self::assertNotEmpty($matches[0]);
        self::assertSame($matches[0], array_values(array_unique($matches[0])));
    }

    public function testLlmsTxtExposesSeoAiOwnerPages(): void
    {
        $content = (string) file_get_contents(dirname(__DIR__).'/public/llms.txt');

        foreach ([
            'https://oling.fr/amoa-si',
            'https://oling.fr/business-apps/erp',
            'https://oling.fr/expertises-audit/rgpd',
            'https://oling.fr/expertises-audit/qse',
            'https://oling.fr/cyber-securite',
        ] as $url) {
            self::assertStringContainsString($url, $content);
        }
    }
}
