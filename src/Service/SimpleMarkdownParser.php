<?php

namespace App\Service;

class SimpleMarkdownParser
{
    public function toHtml(string $markdown): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $markdown) ?: [];

        $html = [];
        $inList = false;
        $paragraph = [];

        $flushParagraph = function () use (&$paragraph, &$html): void {
            if ($paragraph === []) {
                return;
            }

            $text = trim(implode(' ', $paragraph));
            if ($text !== '') {
                $html[] = '<p>' . $this->formatInline($text) . '</p>';
            }
            $paragraph = [];
        };

        $closeList = function () use (&$inList, &$html): void {
            if ($inList) {
                $html[] = '</ul>';
                $inList = false;
            }
        };

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                $flushParagraph();
                $closeList();
                continue;
            }

            if (preg_match('/^(#{1,3})\s+(.+)$/', $trimmed, $matches) === 1) {
                $flushParagraph();
                $closeList();

                $level = strlen($matches[1]);
                $text = $this->formatInline($matches[2]);
                $html[] = sprintf('<h%d>%s</h%d>', $level, $text, $level);
                continue;
            }

            if (preg_match('/^-\s+(.+)$/', $trimmed, $matches) === 1) {
                $flushParagraph();
                if (!$inList) {
                    $html[] = '<ul>';
                    $inList = true;
                }
                $html[] = '<li>' . $this->formatInline($matches[1]) . '</li>';
                continue;
            }

            $closeList();
            $paragraph[] = $trimmed;
        }

        $flushParagraph();
        $closeList();

        return implode("\n", $html);
    }

    private function formatInline(string $text): string
    {
        $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $escaped = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/', static function (array $matches): string {
            $label = $matches[1];
            $url = htmlspecialchars($matches[2], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            return sprintf('<a href="%s">%s</a>', $url, $label);
        }, $escaped) ?? $escaped;

        $escaped = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $escaped) ?? $escaped;
        $escaped = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $escaped) ?? $escaped;
        $escaped = preg_replace('/`([^`]+)`/', '<code>$1</code>', $escaped) ?? $escaped;

        return $escaped;
    }
}
