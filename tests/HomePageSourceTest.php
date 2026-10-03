<?php

namespace App\Tests;

use App\Controller\PracticeController;
use App\Entity\Metier;
use App\Entity\SitePageTranslation;
use App\Repository\LocalizedSlugHistoryRepository;
use App\Repository\SitePageTranslationRepository;
use App\Service\I18n\LocalizedUrlGenerator;
use App\Service\I18n\LocaleRouteContext;
use App\Service\LegalPageDefaults;
use App\Service\LocalizedContentResolver;
use App\Service\PublicSitePageResolver;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class HomePageSourceTest extends TestCase
{
    public function testHomePageSourcesContainCompleteLocalizedHomePayloads(): void
    {
        foreach (['fr', 'en', 'es'] as $locale) {
            $path = dirname(__DIR__) . sprintf('/data/i18n/home_page.%s.json', $locale);
            $source = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

            self::assertSame('home', $source['slug']);
            self::assertSame($locale, $source['locale']);
            self::assertNotEmpty($source['homePage']['hero']['titleLines']);
            self::assertCount(4, $source['homePage']['practices']['cards']);
            self::assertNotEmpty($source['homePage']['accompaniments']['title']);
            self::assertGreaterThan(0, count($source['homePage']['accompaniments']['items']));
            self::assertNotEmpty($source['homePage']['proof']['title']);
            self::assertGreaterThan(0, count($source['homePage']['proof']['items']));
            self::assertNotEmpty($source['homePage']['projects']['title']);
            self::assertNotEmpty($source['homePage']['resources']['title']);
            self::assertNotEmpty($source['homePage']['finalCta']['title']);
            self::assertNotEmpty($source['homePage']['finalCta']['text']);
        }
    }

    public function testLocalizedHomeHeroMetiersDoNotExposeFrenchBusinessAreaLabels(): void
    {
        $method = new \ReflectionMethod(PracticeController::class, 'buildHomeHeroMetiers');
        $method->setAccessible(true);
        $controller = $this->controller();

        $water = (new Metier())
            ->setSlug('eauetassainissement')
            ->setDesignation('Eau & assainissement')
            ->setImageHero('/images/water.jpg')
            ->setHomeHeroText1('Eau & assainissement');
        $bank = (new Metier())
            ->setSlug('banque')
            ->setDesignation('Banque')
            ->setImageHero('/images/bank.jpg')
            ->setHomeHeroText1('Banque');

        foreach ([SitePageTranslation::LOCALE_EN, SitePageTranslation::LOCALE_ES] as $locale) {
            $payload = $method->invoke($controller, [$water, $bank], $locale);
            $encoded = json_encode($payload, JSON_THROW_ON_ERROR);

            self::assertNotEmpty($payload);
            self::assertStringNotContainsString('Eau & assainissement', $encoded);
            self::assertStringNotContainsString('Banque', $encoded);
        }
    }

    public function testLegalPagesHaveNonEmptyLocalizedSourceBodies(): void
    {
        $expected = ['mentions-legales', 'polrgpd', 'polsecurite', 'charte-ia'];
        $sources = [
            SitePageTranslation::LOCALE_FR => LegalPageDefaults::defaults(),
            SitePageTranslation::LOCALE_EN => json_decode((string) file_get_contents(dirname(__DIR__).'/data/i18n/legal_pages.en.json'), true, 512, JSON_THROW_ON_ERROR),
            SitePageTranslation::LOCALE_ES => json_decode((string) file_get_contents(dirname(__DIR__).'/data/i18n/legal_pages.es.json'), true, 512, JSON_THROW_ON_ERROR),
        ];

        foreach ($sources as $locale => $pages) {
            foreach ($expected as $slug) {
                self::assertNotEmpty(trim((string) ($pages[$slug]['title'] ?? '')), sprintf('%s %s title is empty', $locale, $slug));
                self::assertGreaterThan(200, strlen(strip_tags((string) ($pages[$slug]['body'] ?? ''))), sprintf('%s %s body is empty', $locale, $slug));
            }
        }
    }

    public function testLegalPageFallbackWorksWithoutPersistedDatabaseRow(): void
    {
        $method = new \ReflectionMethod(PracticeController::class, 'localizeLegalPage');
        $method->setAccessible(true);

        $view = $method->invoke($this->controller(), null, SitePageTranslation::LOCALE_EN, 'mentions-legales');

        self::assertSame('Legal notices and general terms and conditions for the oling.fr website', $view->getTitle());
        self::assertStringContainsString('Website publisher', $view->getBody());
    }

    public function testCookieCriticalLabelsAreLocalized(): void
    {
        foreach ([
            'fr' => ['Gestion des cookies', 'Accepter tout', 'Refuser tout (minimum)', 'Enregistrer mes choix'],
            'en' => ['Cookie settings', 'Accept all', 'Refuse all', 'Save choices'],
            'es' => ['Gestión de cookies', 'Aceptar todo', 'Rechazar todo', 'Guardar opciones'],
        ] as $locale => $expectedLabels) {
            $messages = Yaml::parseFile(dirname(__DIR__).sprintf('/translations/messages.%s.yaml', $locale));
            $cookies = $messages['cookies'] ?? [];

            foreach (['title', 'accept_all', 'refuse_all', 'save'] as $index => $key) {
                self::assertSame($expectedLabels[$index], $cookies[$key] ?? null);
            }
        }
    }

    public function testLocalizedLegalRoutesDeclareLocaleDefaults(): void
    {
        $controller = (string) file_get_contents(dirname(__DIR__).'/src/Controller/PracticeController.php');

        foreach ([
            "name: 'discloser_en', defaults: ['_locale' => SitePageTranslation::LOCALE_EN]",
            "name: 'discloser_es', defaults: ['_locale' => SitePageTranslation::LOCALE_ES]",
            "name: 'charte_ia_en', defaults: ['_locale' => SitePageTranslation::LOCALE_EN]",
            "name: 'charte_ia_es', defaults: ['_locale' => SitePageTranslation::LOCALE_ES]",
            "name: 'polrgpd_en', defaults: ['_locale' => SitePageTranslation::LOCALE_EN]",
            "name: 'polrgpd_es', defaults: ['_locale' => SitePageTranslation::LOCALE_ES]",
            "name: 'polsecurite_en', defaults: ['_locale' => SitePageTranslation::LOCALE_EN]",
            "name: 'polsecurite_es', defaults: ['_locale' => SitePageTranslation::LOCALE_ES]",
        ] as $needle) {
            self::assertStringContainsString($needle, $controller);
        }
    }

    private function controller(): PracticeController
    {
        $localizedResolver = new LocalizedContentResolver(
            $this->createMock(SitePageTranslationRepository::class),
            $this->createMock(LocalizedSlugHistoryRepository::class),
            $this->createMock(Connection::class)
        );

        return new PracticeController(
            $this->createMock(PublicSitePageResolver::class),
            $localizedResolver,
            new LocalizedUrlGenerator($localizedResolver, new LocaleRouteContext())
        );
    }
}
