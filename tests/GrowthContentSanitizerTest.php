<?php

namespace App\Tests;

use App\Service\GrowthContentSanitizer;
use PHPUnit\Framework\TestCase;

class GrowthContentSanitizerTest extends TestCase
{
    public function testDangerousMarkupIsRemoved(): void
    {
        $sanitizer = new GrowthContentSanitizer();

        $result = $sanitizer->sanitizeHtml('<p onclick="alert(1)">Bonjour</p><script>alert(2)</script><a href="javascript:alert(3)">x</a>');

        self::assertStringNotContainsString('onclick', $result);
        self::assertStringNotContainsString('<script', $result);
        self::assertStringNotContainsString('javascript:', $result);
        self::assertStringContainsString('<p>Bonjour</p>', $result);
    }
}
