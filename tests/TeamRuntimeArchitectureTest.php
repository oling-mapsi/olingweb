<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

final class TeamRuntimeArchitectureTest extends TestCase
{
    public function testPublicTeamRuntimeIsDatabaseDriven(): void
    {
        $controller = (string) file_get_contents(dirname(__DIR__) . '/src/Controller/PracticeController.php');
        $repository = (string) file_get_contents(dirname(__DIR__) . '/src/Repository/TeamRepository.php');

        self::assertStringContainsString('findPublishedOrderedForLocale($locale)', $controller);
        self::assertStringNotContainsString('PUBLIC_TEAM_ORDER', $controller);
        foreach ($this->canonicalNames() as $name) {
            self::assertStringNotContainsString($name, $controller);
            self::assertStringNotContainsString($name, $repository);
        }

        self::assertStringContainsString('team.is_public = 1', $repository);
        self::assertStringContainsString('ORDER BY team.display_order ASC', $repository);
        self::assertStringContainsString('translation.public_profile IS NOT NULL', $repository);
        self::assertStringContainsString('translation.translation_status = :status', $repository);
    }

    public function testRuntimeDoesNotReadTeamImportJsonFiles(): void
    {
        foreach (['src/Controller', 'src/Service', 'src/Repository', 'templates'] as $directory) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(dirname(__DIR__) . '/' . $directory));
            foreach ($iterator as $file) {
                if (!$file->isFile()) {
                    continue;
                }
                $contents = (string) file_get_contents($file->getPathname());
                self::assertStringNotContainsString('team_profiles.fr.json', $contents, $file->getPathname());
                self::assertStringNotContainsString('team.wave3.en.json', $contents, $file->getPathname());
                self::assertStringNotContainsString('team.wave3.es.json', $contents, $file->getPathname());
            }
        }
    }

    public function testExpectedPublicTeamDataIsConfiguredInMigrationNotController(): void
    {
        $migration = (string) file_get_contents(dirname(__DIR__) . '/migrations/Version20261003120000.php');

        foreach ($this->canonicalNames() as $index => $name) {
            self::assertStringContainsString($name, $migration);
            self::assertStringContainsString((string) ($index + 1) . ' =>', $migration);
        }
        self::assertStringContainsString('is_public = 0', $migration);
    }

    /**
     * @return string[]
     */
    private function canonicalNames(): array
    {
        return [
            'Florestan Rouet',
            'Dorothée Maitrias',
            'Manuel Feuillard',
            'Hanna Badan',
            'Julien Pujol',
            'Claire Tillion',
            'Jean-Claude Vati',
        ];
    }
}
