<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class TeamProfilesSourceTest extends TestCase
{
    public function testTeamProfilesSourceContainsExistingFrenchProfiles(): void
    {
        $source = json_decode((string) file_get_contents(dirname(__DIR__) . '/data/i18n/team_profiles.fr.json'), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('fr', $source['locale']);
        self::assertCount(6, $source['profiles']);
        self::assertSame('florestan rouet', $source['profiles'][0]['name']);
    }
}
