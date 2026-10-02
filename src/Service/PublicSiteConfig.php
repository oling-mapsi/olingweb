<?php

namespace App\Service;

class PublicSiteConfig
{
    public function getHome(): array
    {
        return [
            'seoTitle' => '',
            'metaDescription' => '',
            'hero' => [
                'eyebrow' => '',
                'titleLines' => [],
                'intro' => '',
                'secondaryCta' => [],
                'tags' => [],
                'portraitImage' => '',
                'portraitAlt' => '',
                'statement' => ['eyebrow' => '', 'title' => '', 'text' => ''],
                'signal' => ['eyebrow' => '', 'title' => '', 'text' => ''],
            ],
            'kpisSection' => ['eyebrow' => '', 'title' => ''],
            'kpis' => [],
            'practices' => ['eyebrow' => '', 'title' => '', 'intro' => '', 'cards' => []],
            'accompaniments' => ['eyebrow' => '', 'title' => '', 'items' => []],
            'proof' => ['eyebrow' => '', 'title' => '', 'items' => [], 'image' => '', 'imageAlt' => ''],
            'projects' => ['eyebrow' => '', 'title' => '', 'intro' => ''],
            'resources' => ['eyebrow' => '', 'title' => '', 'intro' => '', 'cta' => []],
            'finalCta' => ['eyebrow' => '', 'title' => '', 'text' => '', 'primaryCta' => []],
        ];
    }
}
