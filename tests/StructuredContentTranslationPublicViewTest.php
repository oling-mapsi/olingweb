<?php

namespace App\Tests;

use App\Entity\Practice;
use App\Repository\SitePageTranslationRepository;
use App\Service\LocalizedContentResolver;
use App\Service\StructuredContentTranslationSynchronizer;
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

    public function testPracticeTranslationSynchronizesFrenchSourceToLegacyFields(): void
    {
        $practice = (new Practice())
            ->setDesignation('LEGACY TITLE')
            ->setIntroductionShort('LEGACY INTRO')
            ->setDescription('LEGACY BODY');

        $reflection = new \ReflectionClass($practice);
        $id = $reflection->getProperty('id');
        $id->setAccessible(true);
        $id->setValue($practice, 456);
        $slug = $reflection->getProperty('slug');
        $slug->setAccessible(true);
        $slug->setValue($practice, 'legacy-slug');

        $connection = $this->createMock(Connection::class);
        $connection->method('fetchAssociative')->willReturn([
            'designation' => 'TRANSLATED ADMIN TITLE',
            'designation_short' => 'TRANSLATED ADMIN SHORT',
            'h1_title' => 'TRANSLATED ADMIN H1',
            'introduction' => 'TRANSLATED ADMIN INTRO LONG',
            'introduction_short' => 'TRANSLATED ADMIN INTRO',
            'description' => 'TRANSLATED ADMIN BODY',
            'description_short' => 'TRANSLATED ADMIN DESCRIPTION SHORT',
            'tags' => '["#ADMIN"]',
        ]);

        $synchronizer = new StructuredContentTranslationSynchronizer($connection);
        $synchronizer->syncPracticeTranslationToLegacy($practice);

        self::assertSame('TRANSLATED ADMIN TITLE', $practice->getDesignation());
        self::assertSame('TRANSLATED ADMIN INTRO', $practice->getIntroductionShort());
        self::assertSame('TRANSLATED ADMIN BODY', $practice->getDescription());
        self::assertSame(['#ADMIN'], $practice->getTags());
    }
}
