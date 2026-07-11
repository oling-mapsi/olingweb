<?php

namespace App\Service;

class SitePageFaqParser
{
    /**
     * Parse FAQ items from a JSON payload stored in site_page.body_html.
     * Expected format:
     * [
     *   {"question":"...","answer":"..."},
     *   {"q":"...","a":"..."}
     * ]
     * or {"items":[...]}.
     */
    public function parse(?string $raw): array
    {
        if ($raw === null || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        $sourceItems = $decoded;
        if (array_key_exists('items', $decoded) && is_array($decoded['items'])) {
            $sourceItems = $decoded['items'];
        }

        $items = [];
        foreach ($sourceItems as $item) {
            if (!is_array($item)) {
                continue;
            }

            $question = trim((string) ($item['question'] ?? $item['q'] ?? ''));
            $answer = trim((string) ($item['answer'] ?? $item['a'] ?? ''));

            if ($question === '' || $answer === '') {
                continue;
            }

            $items[] = [
                'question' => $question,
                'answer' => $answer,
            ];

            if (count($items) >= 10) {
                break;
            }
        }

        return $items;
    }
}
