<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class TeamProfilesSourceTest extends TestCase
{
    public function testTeamProfilesSourceContainsExistingFrenchProfiles(): void
    {
        $source = json_decode((string) file_get_contents(dirname(__DIR__) . '/data/i18n/team_profiles.fr.json'), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('fr', $source['locale']);
        self::assertSame([
            'Florestan Rouet',
            'Dorothée Maitrias',
            'Manuel Feuillard',
            'Hanna Badan',
            'Julien Pujol',
            'Claire Tillion',
            'Jean-Claude Vati',
        ], array_column($source['profiles'], 'displayName'));
        self::assertNotContains('Gilbert Rinaldo', array_column($source['profiles'], 'displayName'));
    }

    public function testTranslatedTeamWavesKeepSamePublicProfiles(): void
    {
        foreach (['en', 'es'] as $locale) {
            $source = json_decode((string) file_get_contents(dirname(__DIR__) . sprintf('/data/i18n/waves/team.wave3.%s.json', $locale)), true, 512, JSON_THROW_ON_ERROR);
            $profiles = array_values(array_filter(array_column($source['translations'], 'publicProfile')));
            $order = array_flip([
                'Florestan Rouet',
                'Dorothée Maitrias',
                'Manuel Feuillard',
                'Hanna Badan',
                'Julien Pujol',
                'Claire Tillion',
                'Jean-Claude Vati',
            ]);
            usort($profiles, static fn (array $a, array $b): int => ($order[$a['displayName']] ?? 99) <=> ($order[$b['displayName']] ?? 99));

            self::assertSame([
                'Florestan Rouet',
                'Dorothée Maitrias',
                'Manuel Feuillard',
                'Hanna Badan',
                'Julien Pujol',
                'Claire Tillion',
                'Jean-Claude Vati',
            ], array_column($profiles, 'displayName'));
            self::assertNotContains('Gilbert Rinaldo', array_column($profiles, 'displayName'));
        }
    }

    public function testTeamTemplateUsesLinkedinIconOnly(): void
    {
        $template = (string) file_get_contents(dirname(__DIR__) . '/templates/team.html.twig');

        self::assertStringContainsString('bi-linkedin', $template);
        self::assertStringContainsString('noopener noreferrer', $template);
        self::assertStringContainsString('aria-label="{{ \'team.linkedin\'|trans', $template);
        self::assertStringNotContainsString('>LinkedIn</a>', $template);
    }
}
