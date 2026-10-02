<?php

namespace App\Tests;

use App\Entity\SitePageTranslation;
use App\Form\SitePageTranslationType;
use App\Repository\SitePageTranslationRepository;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;

class SitePageTranslationTypeTest extends TypeTestCase
{
    private SitePageTranslationRepository $translationRepository;

    public function testFrenchSlugCollisionIsRejectedBeforeSql(): void
    {
        $existing = (new SitePageTranslation())
            ->setLocale('fr')
            ->setSlug('erp-progiciel')
            ->setTitle('Existing');
        $this->assignTranslationId($existing, 10);

        $this->translationRepository
            ->method('findOneBy')
            ->willReturn($existing);

        $translation = (new SitePageTranslation())
            ->setLocale('fr')
            ->setSlug('old-slug')
            ->setTitle('Current');
        $this->assignTranslationId($translation, 11);

        $form = $this->factory->create(SitePageTranslationType::class, $translation);
        $form->submit([
            'translationStatus' => SitePageTranslation::STATUS_DRAFT,
            'slug' => 'erp-progiciel',
            'title' => 'Current',
            'seoDescription' => '',
            'heroBadge' => '',
            'heroTitle' => '',
            'heroIntro' => '',
            'heroSideHtml' => '',
            'bodyHtml' => '',
            'publishedAt' => '',
            'unpublishedAt' => '',
            'heroImage' => '',
        ]);

        self::assertFalse($form->isValid());
        self::assertSame('Ce slug existe deja pour cette langue.', (string) $form->get('slug')->getErrors()[0]->getMessage());
    }

    protected function getExtensions(): array
    {
        $this->translationRepository = $this->createMock(SitePageTranslationRepository::class);

        return [
            new PreloadedExtension([
                new SitePageTranslationType($this->translationRepository),
            ], []),
            new ValidatorExtension(Validation::createValidator()),
        ];
    }

    private function assignTranslationId(SitePageTranslation $translation, int $id): void
    {
        $property = new \ReflectionProperty(SitePageTranslation::class, 'id');
        $property->setAccessible(true);
        $property->setValue($translation, $id);
    }
}
