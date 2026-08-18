<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class SitemapDumpCommandTest extends KernelTestCase
{
    public function testSitemapDumpCompletesAndGeneratesCanonicalUrls(): void
    {
        $databaseUrl = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? getenv('DATABASE_URL');
        if (!is_string($databaseUrl) || $databaseUrl === '') {
            self::markTestSkipped('DATABASE_URL is not available in the test environment.');
        }

        self::bootKernel([
            'environment' => 'prod',
            'debug' => false,
        ]);

        $application = new Application(self::$kernel);
        $command = $application->find('presta:sitemaps:dump');
        $targetDir = sys_get_temp_dir() . '/oling-sitemap-' . bin2hex(random_bytes(4));

        if (!mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
            self::fail('Unable to create temporary sitemap target directory.');
        }

        try {
            $tester = new CommandTester($command);
            $exitCode = $tester->execute([
                'command' => $command->getName(),
                'target' => $targetDir,
            ]);

            self::assertSame(0, $exitCode, $tester->getDisplay(true));

            $defaultSitemap = $targetDir . '/sitemap.default.xml';
            self::assertFileExists($defaultSitemap);

            $xml = file_get_contents($defaultSitemap);
            self::assertIsString($xml);
            self::assertStringContainsString('https://oling.fr/amoa-si', $xml);
            self::assertStringContainsString('https://oling.fr/business-apps/erp', $xml);
            self::assertStringContainsString('https://oling.fr/crm', $xml);
            self::assertStringContainsString('https://oling.fr/gmao', $xml);
            self::assertStringContainsString('https://oling.fr/si-finance', $xml);
            self::assertStringContainsString('https://oling.fr/expertises-audit/rgpd', $xml);
            self::assertStringContainsString('https://oling.fr/mapsi/integration-progiciel', $xml);
            self::assertStringNotContainsString('https://www.oling.fr', $xml);
            self::assertStringNotContainsString('http://oling.fr', $xml);
            self::assertStringNotContainsString('pilot-oling-e2e-article', $xml);
        } finally {
            $files = glob($targetDir . '/*');
            if (is_array($files)) {
                foreach ($files as $file) {
                    @unlink($file);
                }
            }
            @rmdir($targetDir);
        }
    }
}
