<?php

declare(strict_types=1);

namespace App\Tests;

use App\Service\PublicSitePageResolver;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class InstitutionalSeoFallbackTest extends TestCase
{
    public function testPublishedLocalizedCmsValueAlwaysWins(): void
    {
        $method = new ReflectionMethod(PublicSitePageResolver::class, 'editorialMetaDescription');

        self::assertSame(
            'Approved CMS override',
            $method->invoke(null, 'apropos', 'en', ' Approved CMS override ', 'Structured text')
        );
        self::assertSame(
            'Approved structured text',
            $method->invoke(null, 'team', 'es', '', ' Approved structured text ')
        );
    }

    public function testOnlyReviewedInstitutionalTranslationsGetFallback(): void
    {
        $method = new ReflectionMethod(PublicSitePageResolver::class, 'editorialMetaDescription');

        foreach (['en', 'es'] as $locale) {
            $source = dirname(__DIR__).'/data/i18n/waves/site_pages.wave1.'.$locale.'.json';
            self::assertFileExists($source);
            $snapshot = json_decode((string) file_get_contents($source), true, 512, JSON_THROW_ON_ERROR);
            $matched = 0;

            foreach ($snapshot['translations'] as $row) {
                $slug = $row['source']['slug'] ?? '';
                if (!in_array($slug, ['apropos', 'team'], true)) {
                    continue;
                }
                ++$matched;
                self::assertSame(
                    $row['seoDescription'],
                    $method->invoke(null, $slug, $locale, null, null),
                    sprintf('%s/%s must use the reviewed published description', $locale, $slug),
                );
            }

            self::assertSame(2, $matched);
        }

        self::assertSame('', $method->invoke(null, 'apropos', 'fr', null, null));
        self::assertSame('', $method->invoke(null, 'unrelated', 'en', null, null));
    }
}
