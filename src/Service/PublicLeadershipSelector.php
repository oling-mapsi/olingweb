<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Editorial selection of OLING leadership profiles for institutional pages.
 *
 * Slugs are stable public-profile identifiers, not database IDs or translated
 * display names. Each selected profile must still be public and published in
 * the requested locale before it reaches this selector.
 *
 * Other published team members remain stored, searchable through their
 * existing service/project relationships, and editable in the back office.
 */
final class PublicLeadershipSelector
{
    private const FEATURED_SLUGS = [
        'florestan-rouet',
        'dorothee-maitrias',
    ];

    /**
     * @param array<int, array<string, mixed>> $profiles Published, localized public profiles
     * @return array<int, array<string, mixed>> Available leadership profiles in editorial order
     */
    public function select(array $profiles): array
    {
        $bySlug = [];
        foreach ($profiles as $profile) {
            $slug = $profile['slug'] ?? null;
            if (is_string($slug) && in_array($slug, self::FEATURED_SLUGS, true) && !isset($bySlug[$slug])) {
                $bySlug[$slug] = $profile;
            }
        }

        $featured = [];
        foreach (self::FEATURED_SLUGS as $slug) {
            if (isset($bySlug[$slug])) {
                $featured[] = $bySlug[$slug];
            }
        }

        return $featured;
    }
}
