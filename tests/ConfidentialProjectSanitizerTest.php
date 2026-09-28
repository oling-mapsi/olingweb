<?php

namespace App\Tests;

use App\Service\Chat\ConfidentialProjectSanitizer;
use PHPUnit\Framework\TestCase;

final class ConfidentialProjectSanitizerTest extends TestCase
{
    public function testRemovesKnownOrganizationName(): void
    {
        $sanitizer = new ConfidentialProjectSanitizer();
        $result = $sanitizer->sanitize('Pour ACME INDUSTRIE, cadrage ERP et reprise de données.', ['ACME INDUSTRIE']);

        self::assertStringNotContainsString('ACME', $result);
        self::assertStringContainsString('cadrage ERP', $result);
    }

    public function testBlocksLegacyReferenceBeforeSafeRebuild(): void
    {
        $sanitizer = new ConfidentialProjectSanitizer();

        self::assertStringNotContainsString(
            'ACME',
            $sanitizer->safeIndexedText('Mission confidentielle pour ACME.', ['erp'])
        );
        self::assertSame(
            'Texte public sûr',
            $sanitizer->safeIndexedText('Texte public sûr', [ConfidentialProjectSanitizer::SAFE_INDEX_MARKER])
        );
    }
}
