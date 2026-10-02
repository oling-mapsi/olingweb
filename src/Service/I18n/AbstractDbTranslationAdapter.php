<?php

namespace App\Service\I18n;

use Doctrine\DBAL\Connection;

abstract class AbstractDbTranslationAdapter implements EntityTranslationAdapterInterface
{
    public function __construct(protected readonly Connection $connection)
    {
    }

    protected function normalizeSlug(string $slug): string
    {
        $slug = trim(mb_strtolower($slug));
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug) ?: $slug;
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? $slug;

        return trim($slug, '-');
    }

    protected function assertPlaceholdersPreserved(string $source, string $target, string $field): void
    {
        $pattern = '/(\\{\\{\\s*[^}]+\\s*\\}\\}|%[a-zA-Z0-9_]+%|\\{[a-zA-Z0-9_]+\\})/';
        preg_match_all($pattern, $source, $sourceMatches);
        preg_match_all($pattern, $target, $targetMatches);
        $sourceTokens = array_values(array_unique($sourceMatches[0]));
        $targetTokens = array_values(array_unique($targetMatches[0]));
        sort($sourceTokens);
        sort($targetTokens);
        if ($sourceTokens !== $targetTokens) {
            throw new AiTranslationValidationException(sprintf('Placeholders mismatch in "%s".', $field));
        }
    }

    protected function assertSameJsonShape(mixed $source, mixed $target, string $path): void
    {
        if ($source === null) {
            return;
        }
        if (is_array($source) !== is_array($target)) {
            throw new AiTranslationValidationException(sprintf('Translated JSON shape mismatch at "%s".', $path));
        }
        if (!is_array($source) || !is_array($target)) {
            return;
        }

        $sourceIsList = array_is_list($source);
        if ($sourceIsList) {
            if (!array_is_list($target) || count($source) !== count($target)) {
                throw new AiTranslationValidationException(sprintf('Translated JSON list shape mismatch at "%s".', $path));
            }
            foreach ($source as $index => $value) {
                $this->assertSameJsonShape($value, $target[$index] ?? null, $path.'.'.$index);
            }
            return;
        }

        $sourceKeys = array_keys($source);
        $targetKeys = array_keys($target);
        sort($sourceKeys);
        sort($targetKeys);
        if ($sourceKeys !== $targetKeys) {
            throw new AiTranslationValidationException(sprintf('Translated JSON keys mismatch at "%s".', $path));
        }
        foreach ($source as $key => $value) {
            $this->assertSameJsonShape($value, $target[$key] ?? null, $path.'.'.$key);
        }
    }

    protected function decodeJson(mixed $value): mixed
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
    }

    protected function encodeJson(mixed $value): ?string
    {
        return $value === null ? null : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    protected function rowsEqual(array $left, array $right): bool
    {
        return json_encode($left, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
            === json_encode($right, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}
