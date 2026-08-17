<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class SeoRegressionTest extends TestCase
{
    public function testCanonicalBaseUrlIsPinnedToNonWwwHost(): void
    {
        $baseTemplate = file_get_contents(__DIR__ . '/../templates/base.html.twig');
        self::assertIsString($baseTemplate);
        self::assertStringContainsString("site_base_url ~ app.request.pathInfo", $baseTemplate);
        self::assertStringNotContainsString('app.request.schemeAndHttpHost ~ app.request.pathInfo', $baseTemplate);
    }

    public function testRobotsTxtExplicitlyAllowsOaiSearchBot(): void
    {
        $robots = file_get_contents(__DIR__ . '/../public/robots.txt');
        self::assertIsString($robots);
        self::assertStringContainsString("User-agent: OAI-SearchBot\nAllow: /", $robots);
        self::assertStringContainsString("User-agent: GPTBot\nAllow: /", $robots);
        self::assertStringContainsString('Sitemap: https://oling.fr/sitemap.xml', $robots);
    }

    public function testIso27001TypoIsGoneFromPublicSiteConfig(): void
    {
        $config = file_get_contents(__DIR__ . '/../src/Service/PublicSiteConfig.php');
        self::assertIsString($config);
        self::assertStringNotContainsString('IS027001', $config);
        self::assertStringContainsString('ISO 27001', $config);
    }

    public function testCanonicalHostRedirectSubscriberRedirectsWwwToNonWww(): void
    {
        $subscriber = file_get_contents(__DIR__ . '/../src/EventListener/CanonicalHostRedirectSubscriber.php');
        self::assertIsString($subscriber);
        self::assertStringContainsString("currentHost !== 'www.oling.fr'", $subscriber);
        self::assertStringContainsString('RedirectResponse::HTTP_MOVED_PERMANENTLY', $subscriber);
        self::assertStringContainsString("\$targetBaseUrl . \$request->getRequestUri()", $subscriber);
    }

    public function testIso27001DatabaseMigrationExists(): void
    {
        $migration = file_get_contents(__DIR__ . '/../migrations/Version20260817110000.php');
        self::assertIsString($migration);
        self::assertStringContainsString("slug = 'si'", $migration);
        self::assertStringContainsString('ISO 27001', $migration);
        self::assertStringContainsString('IS027001', $migration);
    }

    public function testApacheHtaccessRedirectsToHttpsNonWww(): void
    {
        $htaccess = file_get_contents(__DIR__ . '/../public/.htaccess');
        self::assertIsString($htaccess);
        self::assertStringContainsString('RewriteCond %{HTTPS} !=on [OR]', $htaccess);
        self::assertStringContainsString('RewriteCond %{HTTP_HOST} ^www\\.oling\\.fr$ [NC]', $htaccess);
        self::assertStringContainsString('RewriteRule ^ https://oling.fr%{REQUEST_URI} [R=301,L]', $htaccess);
    }

    public function testPilotE2eArticleSlugIsBlockedFromGrowthPublishing(): void
    {
        $service = file_get_contents(__DIR__ . '/../src/Service/GrowthPublishingService.php');
        self::assertIsString($service);
        self::assertStringContainsString("'pilot-oling-e2e-article'", $service);
        self::assertStringContainsString('This slug is blocked and cannot be published.', $service);
    }

    public function testPilotE2eDatabaseMigrationExists(): void
    {
        $migration = file_get_contents(__DIR__ . '/../migrations/Version20260817113000.php');
        self::assertIsString($migration);
        self::assertStringContainsString('ressource-pilot-oling-e2e-article', $migration);
        self::assertStringContainsString("publication_status = 'unpublished'", $migration);
    }

    public function testLegacyPublicTestRouteIsNotDeclared(): void
    {
        $routes = file_get_contents(__DIR__ . '/../config/routes.yaml');
        self::assertIsString($routes);
        self::assertStringNotContainsString('/test', $routes);
        self::assertStringNotContainsString('App\\Controller\\TestController::index', $routes);
    }
}
