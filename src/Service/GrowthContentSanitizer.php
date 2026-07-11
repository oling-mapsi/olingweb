<?php

namespace App\Service;

class GrowthContentSanitizer
{
    private const ALLOWED_TAGS = [
        'a' => ['href', 'title', 'target', 'rel'],
        'blockquote' => [],
        'br' => [],
        'code' => [],
        'em' => [],
        'h2' => [],
        'h3' => [],
        'h4' => [],
        'img' => ['src', 'alt', 'title', 'loading'],
        'li' => [],
        'ol' => [],
        'p' => [],
        'pre' => [],
        'strong' => [],
        'table' => [],
        'tbody' => [],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan'],
        'thead' => [],
        'tr' => [],
        'ul' => [],
    ];

    public function sanitizeHtml(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $document = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?><div id="growth-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('growth-root');
        if (!$root instanceof \DOMElement) {
            return '';
        }

        $this->sanitizeNode($root);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        return trim($output);
    }

    public function sanitizeText(?string $text, int $maxLength = 255): string
    {
        $normalized = trim(strip_tags((string) $text));

        return mb_substr($normalized, 0, $maxLength);
    }

    private function sanitizeNode(\DOMNode $node): void
    {
        if ($node instanceof \DOMElement) {
            $tag = strtolower($node->tagName);
            if (!isset(self::ALLOWED_TAGS[$tag]) && $tag !== 'div') {
                $this->unwrapNode($node);
                return;
            }

            if ($tag !== 'div') {
                $this->sanitizeAttributes($node, self::ALLOWED_TAGS[$tag]);
            }
        }

        for ($child = $node->firstChild; $child !== null; $child = $next) {
            $next = $child->nextSibling;
            if ($child instanceof \DOMComment) {
                $node->removeChild($child);
                continue;
            }

            $this->sanitizeNode($child);
        }
    }

    private function sanitizeAttributes(\DOMElement $element, array $allowedAttributes): void
    {
        $toRemove = [];
        foreach ($element->attributes as $attribute) {
            $name = strtolower($attribute->name);
            if (!in_array($name, $allowedAttributes, true) || str_starts_with($name, 'on')) {
                $toRemove[] = $attribute->name;
                continue;
            }

            if (in_array($name, ['href', 'src'], true) && !$this->isSafeUrl($attribute->value)) {
                $toRemove[] = $attribute->name;
            }
        }

        foreach ($toRemove as $name) {
            $element->removeAttribute($name);
        }

        if (strtolower($element->tagName) === 'a' && $element->getAttribute('target') === '_blank') {
            $element->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private function isSafeUrl(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        if (str_starts_with($value, '/')) {
            return true;
        }

        return preg_match('#^(https?:|mailto:|tel:)#i', $value) === 1;
    }

    private function unwrapNode(\DOMElement $element): void
    {
        $parent = $element->parentNode;
        if ($parent === null) {
            return;
        }

        while ($element->firstChild !== null) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }
}
