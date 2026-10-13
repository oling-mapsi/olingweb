<?php

declare(strict_types=1);

namespace App\Tests;

use App\Dto\TranslatedEntityPublicView;
use App\Entity\HomeSection;
use PHPUnit\Framework\TestCase;

final class HomeSectionTwigCompatibilityTest extends TestCase
{
    public function testHomepageCallsExplicitLocalizedGetters(): void
    {
        $template = (string) file_get_contents(dirname(__DIR__).'/templates/index.html.twig');

        self::assertStringContainsString('homePracticesSection.getEyebrow()', $template);
        self::assertStringContainsString('homePracticesSection.getIntro()', $template);
        self::assertStringNotContainsString('homePracticesSection.eyebrow', $template);
        self::assertStringNotContainsString('homePracticesSection.intro', $template);
    }

    public function testHomeSectionTranslatedViewExposesExplicitAccessors(): void
    {
        $section = (new HomeSection())->setSlug('practices')->setEyebrow('Source')->setIntro('Source intro');
        $view = new TranslatedEntityPublicView($section, [
            'eyebrow' => 'Étiquette traduite',
            'intro' => 'Introduction traduite',
        ]);

        self::assertSame('Étiquette traduite', $view->getEyebrow());
        self::assertSame('Introduction traduite', $view->getIntro());

        $fallback = new TranslatedEntityPublicView($section, []);
        self::assertSame('Source', $fallback->getEyebrow());
        self::assertSame('Source intro', $fallback->getIntro());
    }
}
