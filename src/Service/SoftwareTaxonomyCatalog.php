<?php

namespace App\Service;

class SoftwareTaxonomyCatalog
{
    private array $items;

    public function __construct()
    {
        $items = require dirname(__DIR__, 2).'/config/oling/software_taxonomy.php';
        $this->items = is_array($items) ? $items : [];
    }

    public function all(): array
    {
        return $this->items;
    }

    public function findByName(string $name): ?array
    {
        $needle = mb_strtolower(trim($name));

        foreach ($this->items as $item) {
            if (mb_strtolower($item['name']) === $needle) {
                return $item;
            }
        }

        return null;
    }

    public function findBySlug(string $slug): ?array
    {
        $needle = trim($slug);

        foreach ($this->items as $item) {
            if (($item['slug'] ?? null) === $needle) {
                return $item;
            }
        }

        return null;
    }
}
