<?php

namespace App\Service\I18n;

use App\Entity\SitePageTranslation;
use App\Entity\User;

final class TranslationReviewWorkflow
{
    public function markToReview(SitePageTranslation $translation): void
    {
        $this->assertNotFrench($translation);
        $translation->setTranslationStatus(SitePageTranslation::STATUS_TO_REVIEW);
    }

    public function markReviewed(SitePageTranslation $translation, ?User $reviewer = null): void
    {
        $this->assertNotFrench($translation);
        $translation
            ->setTranslationStatus(SitePageTranslation::STATUS_REVIEWED)
            ->setReviewedAt(new \DateTimeImmutable())
            ->setReviewedBy($reviewer);
    }

    public function publish(SitePageTranslation $translation): void
    {
        $this->assertNotFrench($translation);
        $this->assertPublishable($translation);
        $translation
            ->setTranslationStatus(SitePageTranslation::STATUS_PUBLISHED)
            ->setPublishedAt(new \DateTimeImmutable())
            ->setUnpublishedAt(null);
    }

    public function unpublish(SitePageTranslation $translation): void
    {
        $this->assertNotFrench($translation);
        $translation
            ->setTranslationStatus(SitePageTranslation::STATUS_REVIEWED)
            ->setUnpublishedAt(new \DateTimeImmutable());
    }

    private function assertPublishable(SitePageTranslation $translation): void
    {
        if ($translation->getTranslationStatus() !== SitePageTranslation::STATUS_REVIEWED) {
            throw new AiTranslationValidationException('Only reviewed translations can be published.');
        }

        if (trim($translation->getSlug()) === '' || trim($translation->getTitle()) === '') {
            throw new AiTranslationValidationException('A localized slug and title are required before publication.');
        }
    }

    private function assertNotFrench(SitePageTranslation $translation): void
    {
        if ($translation->getLocale() === SitePageTranslation::LOCALE_FR) {
            throw new AiTranslationValidationException('FR source translations are not managed by the AI review workflow.');
        }
    }
}
