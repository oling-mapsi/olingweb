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

    public function testCollectsAndRemovesLongNameAcronymAndAdministeredAlias(): void
    {
        $sanitizer = new ConfidentialProjectSanitizer();
        $identifiers = $sanitizer->collectSensitiveIdentifiers(
            'Compagnie Régionale des Services Numériques',
            ['client_aliases' => ['Horizon Services']]
        );
        $result = $sanitizer->sanitize(
            'Compagnie Régionale des Services Numériques, CRSN et Horizon Services pilotent un PCA pour le secteur de la formation.',
            $identifiers
        );

        self::assertStringNotContainsString('Compagnie Régionale', $result);
        self::assertStringNotContainsString('CRSN', $result);
        self::assertStringNotContainsString('Horizon Services', $result);
        self::assertStringContainsString('PCA', $result);
        self::assertStringContainsString('secteur de la formation', $result);
    }

    public function testCollectsUppercaseShortNameFromOrganizationName(): void
    {
        $sanitizer = new ConfidentialProjectSanitizer();
        $identifiers = $sanitizer->collectSensitiveIdentifiers('ALPHA Territoires');

        self::assertContains('ALPHA', $identifiers);
        self::assertStringNotContainsString('ALPHA', $sanitizer->sanitize('Mission ALPHA de continuité.', $identifiers));
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
