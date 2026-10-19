<?php

namespace App\Service;

use App\Dto\PublicMediaView;
use App\Entity\SitePage;

final class PublicMediaResolver
{
    public function resolveSitePageHero(
        SitePage $page,
        ?object $translation = null,
        ?string $inheritedImage = null,
        ?string $inheritedAlt = null,
        ?string $inheritedSourceLabel = null,
    ): PublicMediaView {
        $mode = $page->getHeroMediaMode();

        if ($mode === SitePage::HERO_MEDIA_MODE_HIDDEN) {
            return new PublicMediaView(null, null, null, null, $mode, 'hidden', null);
        }

        $customImage = $this->normalizePath($page->getHeroImage());
        if ($customImage !== null) {
            return new PublicMediaView(
                $customImage,
                $this->normalizeText($this->imageAlt($translation)) ?: $this->normalizeText($page->getHeroTitle() ?: $page->getTitle()),
                $this->normalizeText($page->getHeroImageCaption()),
                $this->normalizeText($page->getHeroImageFocalPoint()),
                SitePage::HERO_MEDIA_MODE_CUSTOM,
                'site_page',
                sprintf('Page publique %s', $page->getSlug()),
                $page->getHeroImageDisplayMode(),
                $page->getHeroImageSize()
            );
        }

        if ($mode === SitePage::HERO_MEDIA_MODE_INHERIT && $this->normalizePath($inheritedImage) !== null) {
            return new PublicMediaView(
                $this->normalizePath($inheritedImage),
                $this->normalizeText($this->imageAlt($translation)) ?: $this->normalizeText($inheritedAlt),
                $this->normalizeText($page->getHeroImageCaption()),
                $this->normalizeText($page->getHeroImageFocalPoint()),
                $mode,
                'inherited',
                $inheritedSourceLabel,
                $page->getHeroImageDisplayMode(),
                $page->getHeroImageSize()
            );
        }

        return new PublicMediaView(null, null, null, null, $mode, 'none', null);
    }

    public function resolveEntityHero(
        ?string $image,
        ?string $alt = null,
        ?string $sourceLabel = null,
    ): PublicMediaView {
        return new PublicMediaView(
            $this->normalizePath($image),
            $this->normalizeText($alt),
            null,
            null,
            SitePage::HERO_MEDIA_MODE_CUSTOM,
            'entity',
            $sourceLabel
        );
    }

    private function normalizePath(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function imageAlt(?object $translation): ?string
    {
        if ($translation === null) {
            return null;
        }

        if (method_exists($translation, 'getImageAlt')) {
            return $translation->getImageAlt();
        }

        if (method_exists($translation, 'getTranslation')) {
            $source = $translation->getTranslation();
            if (is_object($source) && method_exists($source, 'getImageAlt')) {
                return $source->getImageAlt();
            }
        }

        return null;
    }

    private function normalizeText(?string $value): ?string
    {
        $value = trim(strip_tags((string) $value));

        return $value === '' ? null : $value;
    }
}
