<?php

declare(strict_types=1);

namespace App\Tests;

use App\Service\PublicSitePageResolver;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class SectorVisualAssetsTest extends TestCase
{
    public function testEveryFallbackReferencesAnExistingCommittedImage(): void
    {
        $reflection = new ReflectionClass(PublicSitePageResolver::class);
        $constant = $reflection->getReflectionConstant('SECTOR_HERO_FALLBACKS');

        self::assertNotFalse($constant, 'The fallback images must be explicitly reviewed and versioned.');

        /** @var array<string, string> $assets */
        $assets = $constant->getValue();
        self::assertCount(11, $assets);
        self::assertArrayHasKey('transport', $assets);
        self::assertArrayHasKey('sante', $assets);

        foreach ($assets as $slug => $assetPath) {
            self::assertMatchesRegularExpression('/^[a-z0-9-]+$/', $slug);
            self::assertStringStartsWith('/uploads/metiers/hero/', $assetPath);
            self::assertStringNotContainsString('..', $assetPath);

            $absolutePath = dirname(__DIR__).'/public'.$assetPath;
            self::assertFileExists($absolutePath, $slug.' is missing its sector photo: '.$assetPath);

            $dimensions = getimagesize($absolutePath);
            self::assertIsArray($dimensions, $slug.' photo must be a readable image file.');
            self::assertGreaterThanOrEqual(320, $dimensions[0], $slug.' photo must be adequately wide.');
            self::assertGreaterThanOrEqual(180, $dimensions[1], $slug.' photo must be adequately tall.');
        }
    }

    public function testSectorCardsKeepCmsImagePriorityAndNeverPrintZeroProof(): void
    {
        $resolver = (string) file_get_contents(dirname(__DIR__).'/src/Service/PublicSitePageResolver.php');
        $template = (string) file_get_contents(dirname(__DIR__).'/templates/sectors/index.html.twig');

        self::assertStringContainsString(
            '$metier->getImageHero() ?: $metier->getImage() ?: (self::SECTOR_HERO_FALLBACKS[$slug] ?? null)',
            $resolver
        );
        self::assertStringContainsString('{% if sector.projectCount > 0 %}', $template);
        self::assertStringContainsString('Missions associées', $template);
        self::assertStringContainsString('sector.heroImage', $template);
    }
}
