<?php

namespace App\Service;

use App\Entity\SitePage;
use App\Entity\SitePageTranslation;

class TranslationSourceHasher
{
    public function hashSitePage(SitePage $page): string
    {
        return hash('sha256', $this->canonicalJson([
            'slug' => $page->getSlug(),
            'title' => $page->getTitle(),
            'metaDescription' => $page->getMetaDescription(),
            'heroBadge' => $page->getHeroBadge(),
            'heroTitle' => $page->getHeroTitle(),
            'heroIntro' => $page->getHeroIntro(),
            'heroSideHtml' => $page->getHeroSideHtml(),
            'bodyHtml' => $page->getBodyHtml(),
            'canonicalUrl' => $page->getCanonicalUrl(),
            'categories' => $page->getCategories(),
            'tags' => $page->getTags(),
        ]));
    }

    public function hashSitePageTranslation(SitePageTranslation $translation): string
    {
        return hash('sha256', $this->canonicalJson([
            'slug' => $translation->getSlug(),
            'title' => $translation->getTitle(),
            'seoDescription' => $translation->getSeoDescription(),
            'heroBadge' => $translation->getHeroBadge(),
            'heroTitle' => $translation->getHeroTitle(),
            'heroIntro' => $translation->getHeroIntro(),
            'heroSideHtml' => $translation->getHeroSideHtml(),
            'bodyHtml' => $translation->getBodyHtml(),
        ]));
    }

    /**
     * @param mixed $value
     */
    private function canonicalJson($value): string
    {
        $normalized = $this->normalize($value);

        return json_encode($normalized, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private function normalize($value)
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        if (!is_array($value)) {
            return $value;
        }

        if (!$this->isList($value)) {
            ksort($value);
        }

        foreach ($value as $key => $item) {
            $value[$key] = $this->normalize($item);
        }

        return $value;
    }

    private function isList(array $value): bool
    {
        return $value === [] || array_keys($value) === range(0, count($value) - 1);
    }
}
