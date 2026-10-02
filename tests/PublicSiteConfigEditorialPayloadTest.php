<?php

namespace App\Tests;

use App\Service\PublicSiteConfig;
use PHPUnit\Framework\TestCase;

class PublicSiteConfigEditorialPayloadTest extends TestCase
{
    public function testExpertiseSectorAndPracticeNarrativePayloadsAreRemoved(): void
    {
        self::assertFalse(method_exists(PublicSiteConfig::class, 'getEditorialPages'));
        self::assertFalse(method_exists(PublicSiteConfig::class, 'getExpertisePages'));
        self::assertFalse(method_exists(PublicSiteConfig::class, 'getSectorPages'));
        self::assertFalse(method_exists(PublicSiteConfig::class, 'getPracticeNarrative'));
        self::assertFalse(method_exists(PublicSiteConfig::class, 'getServiceNarrative'));
    }
}
