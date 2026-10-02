<?php

namespace App\Tests;

use App\Entity\Practice;
use App\Repository\SitePageTranslationRepository;
use App\Service\LocalizedContentResolver;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

final class StructuredContentTranslationPublicViewTest extends TestCase
{
    public function testPracticePublicViewReadsFrenchTranslationBeforeLegacyFields(): void
    {
        $practice = (new Practice())
            ->setDesignation('LEGACY TITLE SHOULD NOT APPEAR')
            ->setIntroductionShort('LEGACY INTRO SHOULD NOT APPEAR')
            ->setDescription('LEGACY BODY SHOULD NOT APPEAR');

        $reflection = new \ReflectionClass($practice);
        $id = $reflection->getProperty('id');
        $id->setAccessible(true);
        $id->setValue($practice, 123);
        $slug = $reflection->getProperty('slug');
        $slug->setAccessible(true);
        $slug->setValue($practice, 'legacy-slug');

        $connection = $this->createMock(Connection::class);
        $connection->method('fetchAssociative')->willReturn([
            'designation' => 'TRANSLATED FR TITLE',
            'slug' => 'translated-fr-slug',
            'designation_short' => 'TRANSLATED FR SHORT',
            'h1_title' => 'TRANSLATED FR H1',
            'introduction' => 'TRANSLATED FR INTRO LONG',
            'introduction_short' => 'TRANSLATED FR INTRO',
            'description' => 'TRANSLATED FR BODY',
            'description_short' => 'TRANSLATED FR DESCRIPTION SHORT',
            'tags' => '["#FR"]',
        ]);

        $resolver = new LocalizedContentResolver($this->createMock(SitePageTranslationRepository::class), $connection);
        $view = $resolver->getFrenchPracticeView($practice);

        self::assertSame('TRANSLATED FR TITLE', $view->getDesignation());
        self::assertSame('translated-fr-slug', $view->getSlug());
        self::assertSame('TRANSLATED FR INTRO', $view->getIntroductionShort());
        self::assertSame('TRANSLATED FR BODY', $view->getDescription());
        self::assertSame(['#FR'], $view->getTags());
    }
}
