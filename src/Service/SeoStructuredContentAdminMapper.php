<?php

namespace App\Service;

final class SeoStructuredContentAdminMapper
{
    public const LANDING_TEXT_FIELDS = ['metaTitle', 'metaDescription', 'heroTitle', 'heroIntro', 'promise'];
    public const SERVICE_TEXT_FIELDS = ['metaTitle', 'metaDescription', 'eyebrow', 'headline', 'intro', 'promise'];

    /**
     * @param array<string, mixed>|null $structuredData
     * @return array<string, mixed>
     */
    public function landingNarrativeToFormData(?array $structuredData): array
    {
        $narrative = is_array($structuredData['landingNarrative'] ?? null) ? $structuredData['landingNarrative'] : [];

        return array_merge($this->textFieldsToFormData($narrative, self::LANDING_TEXT_FIELDS), [
            'focus' => $this->listToLines($narrative['focus'] ?? []),
            'citablePassages' => $this->itemsToLines($narrative['citablePassages'] ?? [], ['title', 'text']),
            'supportLinks' => $this->itemsToLines($narrative['supportLinks'] ?? [], ['label', 'href', 'description']),
        ]);
    }

    /**
     * @param array<string, mixed>|null $publicNarrative
     * @return array<string, mixed>
     */
    public function serviceNarrativeToFormData(?array $publicNarrative): array
    {
        return $this->textFieldsToFormData($publicNarrative ?? [], self::SERVICE_TEXT_FIELDS);
    }

    /**
     * @param array<string, mixed>|null $structuredData
     * @param array<string, mixed> $formData
     * @return array<string, mixed>
     */
    public function mergeLandingNarrative(?array $structuredData, array $formData): array
    {
        $structuredData ??= [];
        $narrative = is_array($structuredData['landingNarrative'] ?? null) ? $structuredData['landingNarrative'] : [];
        $narrative = $this->mergeTextFields($narrative, $formData, self::LANDING_TEXT_FIELDS);
        $narrative = $this->mergeOptionalArray($narrative, 'focus', $this->linesToList((string) ($formData['focus'] ?? '')));
        $narrative = $this->mergeOptionalArray($narrative, 'citablePassages', $this->linesToItems((string) ($formData['citablePassages'] ?? ''), ['title', 'text']));
        $narrative = $this->mergeOptionalArray($narrative, 'supportLinks', $this->linesToItems((string) ($formData['supportLinks'] ?? ''), ['label', 'href', 'description']));
        $structuredData['landingNarrative'] = $narrative;

        return $structuredData;
    }

    /**
     * @param array<string, mixed>|null $publicNarrative
     * @param array<string, mixed> $formData
     * @return array<string, mixed>
     */
    public function mergeServiceNarrative(?array $publicNarrative, array $formData): array
    {
        return $this->mergeTextFields($publicNarrative ?? [], $formData, self::SERVICE_TEXT_FIELDS);
    }

    /**
     * @param array<string, mixed> $source
     * @param list<string> $fields
     * @return array<string, string>
     */
    private function textFieldsToFormData(array $source, array $fields): array
    {
        $data = [];
        foreach ($fields as $field) {
            $data[$field] = is_scalar($source[$field] ?? null) ? (string) $source[$field] : '';
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $target
     * @param array<string, mixed> $formData
     * @param list<string> $fields
     * @return array<string, mixed>
     */
    private function mergeTextFields(array $target, array $formData, array $fields): array
    {
        foreach ($fields as $field) {
            $value = trim((string) ($formData[$field] ?? ''));
            if ($value === '') {
                unset($target[$field]);
                continue;
            }
            $target[$field] = $value;
        }

        return $target;
    }

    /**
     * @param mixed $items
     */
    private function listToLines(mixed $items): string
    {
        if (!is_array($items)) {
            return '';
        }

        return implode("\n", array_values(array_filter(array_map(
            static fn (mixed $item): string => is_scalar($item) ? trim((string) $item) : '',
            $items
        ), static fn (string $item): bool => $item !== '')));
    }

    /**
     * @return list<string>
     */
    private function linesToList(string $value): array
    {
        return array_values(array_filter(array_map(
            static fn (string $line): string => trim($line),
            preg_split('/\R/u', $value) ?: []
        ), static fn (string $line): bool => $line !== ''));
    }

    /**
     * @param mixed $items
     * @param list<string> $fields
     */
    private function itemsToLines(mixed $items, array $fields): string
    {
        if (!is_array($items)) {
            return '';
        }

        $lines = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $parts = [];
            foreach ($fields as $field) {
                $parts[] = is_scalar($item[$field] ?? null) ? trim((string) $item[$field]) : '';
            }
            if (implode('', $parts) !== '') {
                $lines[] = implode(' | ', $parts);
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @param list<string> $fields
     * @return list<array<string, string>>
     */
    private function linesToItems(string $value, array $fields): array
    {
        $items = [];
        foreach (preg_split('/\R/u', $value) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $parts = array_map('trim', explode('|', $line));
            if (count($parts) > count($fields)) {
                throw new \InvalidArgumentException(sprintf('Ligne trop longue : "%s".', $line));
            }
            $item = [];
            foreach ($fields as $index => $field) {
                $item[$field] = $parts[$index] ?? '';
            }
            if (trim(implode('', $item)) !== '') {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @param array<string, mixed> $target
     * @param list<mixed> $value
     * @return array<string, mixed>
     */
    private function mergeOptionalArray(array $target, string $key, array $value): array
    {
        if ($value === []) {
            unset($target[$key]);
            return $target;
        }
        $target[$key] = $value;

        return $target;
    }
}
