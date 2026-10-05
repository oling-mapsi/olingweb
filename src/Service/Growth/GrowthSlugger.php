<?php

namespace App\Service\Growth;

class GrowthSlugger
{
    public function slug(string $value): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $slug = strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $ascii));
        $slug = trim((string) preg_replace('/-+/', '-', $slug), '-');

        return $slug !== '' ? $slug : 'growth-content';
    }
}
