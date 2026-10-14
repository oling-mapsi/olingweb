<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;

final class PublicEditorialDesignSourceTest extends TestCase
{
    /**
     * @dataProvider editorialPageTemplates
     */
    public function testPublicPagesOptIntoTheSharedEditorialDesign(string $template): void
    {
        $path = dirname(__DIR__) . '/templates/' . $template;
        self::assertFileExists($path);
        $source = (string) file_get_contents($path);

        self::assertStringContainsString('oling-public-editorial', $source, $template);
        self::assertStringContainsString('<main', $source, $template);
        self::assertStringNotContainsString('noindex', $source, $template);
    }

    /** @return iterable<string, array{string}> */
    public static function editorialPageTemplates(): iterable
    {
        foreach ([
            'expertises/index.html.twig',
            'expertises/show.html.twig',
            'services-index.html.twig',
            'services.html.twig',
            'sectors/index.html.twig',
            'sectors/show.html.twig',
            'projets.html.twig',
            'seo/resources-index.html.twig',
            'seo/resource-article.html.twig',
            'contact.html.twig',
            'practice-home.html.twig',
            'seo/_db-landing.html.twig',
            'metiers.html.twig',
            'rse.html.twig',
            'client.html.twig',
            'practices.html.twig',
            'polrgpd.html.twig',
            'polsecu.html.twig',
            'charte-ia.html.twig',
            'page-terms.html.twig',
            'public/localized_site_page.html.twig',
        ] as $template) {
            yield $template => [$template];
        }
    }

    public function testCssImportsAreOrderedAndExcludeHomepageAndBackoffice(): void
    {
        $entry = (string) file_get_contents(dirname(__DIR__) . '/assets/app.js');
        $secondary = (string) file_get_contents(dirname(__DIR__) . '/assets/styles/public-secondary.css');
        $editorial = (string) file_get_contents(dirname(__DIR__) . '/assets/styles/public-editorial.css');
        $language = (string) file_get_contents(dirname(__DIR__) . '/assets/styles/public-language-switcher.css');

        $imports = [
            "import './styles/app.css';",
            "import './styles/public-secondary.css';",
            "import './styles/public-leadership.css';",
            "import './styles/public-language-switcher.css';",
            "import './styles/public-editorial.css';",
        ];
        $positions = array_map(static fn (string $entryName) => strpos($entry, $entryName), $imports);
        foreach ($positions as $position) {
            self::assertNotFalse($position);
        }
        self::assertSame($positions, array_values(array_unique($positions)));
        $sortedPositions = $positions;
        sort($sortedPositions);
        self::assertSame($sortedPositions, $positions, 'Public CSS must load after the legacy styles in a stable order.');

        self::assertStringContainsString('body.oling-body:not(.oling-home):not(.admin)', $secondary);
        self::assertStringContainsString('body.oling-body:not(.oling-home):not(.admin)', $editorial);
        self::assertStringContainsString('main.oling-public-editorial', $editorial);
        self::assertStringContainsString('body.oling-body:not(.admin)', $language);
        self::assertStringContainsString('::after', $language);
        self::assertStringContainsString('stroke=', $language);
    }

    public function testStoredEditorialMediaRemainConnectedToPublicTemplates(): void
    {
        $hero = (string) file_get_contents(dirname(__DIR__) . '/templates/public/_page_hero.html.twig');
        $service = (string) file_get_contents(dirname(__DIR__) . '/templates/services.html.twig');
        $practice = (string) file_get_contents(dirname(__DIR__) . '/templates/practice-home.html.twig');
        $projects = (string) file_get_contents(dirname(__DIR__) . '/templates/projets.html.twig');
        $miniProjects = (string) file_get_contents(dirname(__DIR__) . '/templates/projets/_mini_cards.html.twig');

        self::assertStringContainsString('oling-shell-hero__media', $hero);
        self::assertStringContainsString('service.getImageHero()', $service);
        self::assertStringContainsString('service.getImage1()', $service);
        self::assertStringContainsString('practice.getImage1()', $practice);
        self::assertStringContainsString('featuredProjects', $projects);
        self::assertStringContainsString('oling-project-media-card__visual', $projects);
        self::assertStringContainsString('projectImageSrc', $miniProjects);
    }

    public function testProjectCardsDoNotReuseAnotherProjectImageAsFallback(): void
    {
        $controller = (string) file_get_contents(dirname(__DIR__) . '/src/Controller/PracticeController.php');

        self::assertStringContainsString('private function resolveProjectCardImage', $controller);
        self::assertStringContainsString('): ?string', $controller);
        self::assertStringContainsString('bool $allowImageFallback = false', $controller);
        self::assertStringContainsString('$this->buildProjectCards($featuredProjects, $imagePool, $locale)', $controller);
        self::assertStringContainsString('$this->buildProjectCards($miniProjects, $imagePool, $locale)', $controller);
        self::assertStringContainsString('$this->buildProjectCards($featuredHomeProjects, $this->buildProjectImagePool($projets), $locale, true)', $controller);
        self::assertStringNotContainsString("return '/img/1920x1080/img5.jpg';", $controller);
    }

    public function testFunctionalProjectFiltersAndContactFormMarkupRemain(): void
    {
        $projects = (string) file_get_contents(dirname(__DIR__) . '/templates/projets.html.twig');
        $contact = (string) file_get_contents(dirname(__DIR__) . '/templates/contact.html.twig');

        self::assertStringContainsString('data-proof-filter=', $projects);
        self::assertStringContainsString('data-proof-category=', $projects);
        self::assertStringContainsString('aria-pressed=', $projects);
        self::assertStringContainsString('id="contact-form"', $contact);
        self::assertStringContainsString('id="consent"', $contact);
        self::assertStringContainsString('id="button-send"', $contact);
        self::assertStringContainsString("csrf_token('contact_form')", $contact);
    }
}
