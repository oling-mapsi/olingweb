<?php

namespace App\Service\I18n;

final class PracticeTranslationAdapter extends AbstractDbTranslationAdapter
{
    private const FIELDS = ['slug', 'designation', 'designationShort', 'h1Title', 'introduction', 'introductionShort', 'description', 'descriptionShort', 'tags'];

    public function entityName(): string
    {
        return 'practice';
    }

    public function selectSources(?int $id, ?string $slug, int $limit): array
    {
        $where = ['fr.locale = :source_locale', 'fr.translation_status = :source_status'];
        $params = ['source_locale' => 'fr', 'source_status' => 'published', 'limit' => $limit];
        if ($id !== null) {
            $where[] = 'p.id = :id';
            $params['id'] = $id;
        }
        if ($slug !== null && $slug !== '') {
            $where[] = 'p.slug = :slug';
            $params['slug'] = $slug;
        }

        return $this->connection->fetchAllAssociative(
            'SELECT p.id source_id, p.slug source_slug, fr.*
             FROM practice p
             INNER JOIN practice_translation fr ON fr.practice_id = p.id
             WHERE '.implode(' AND ', $where).'
             ORDER BY p.slug ASC
             LIMIT :limit',
            $params,
            ['limit' => \PDO::PARAM_INT]
        );
    }

    public function findTarget(int $sourceId, string $locale): ?array
    {
        $row = $this->connection->fetchAssociative('SELECT * FROM practice_translation WHERE practice_id = :id AND locale = :locale', ['id' => $sourceId, 'locale' => $locale]);

        return is_array($row) ? $row : null;
    }

    public function selectTargets(string $locale, array $statuses = []): array
    {
        $where = ['t.locale = :locale'];
        $params = ['locale' => $locale];
        if ($statuses !== []) {
            $where[] = 't.translation_status IN (:statuses)';
            $params['statuses'] = $statuses;
        }

        return $this->connection->fetchAllAssociative(
            'SELECT p.slug source_slug, t.*
             FROM practice_translation t
             INNER JOIN practice p ON p.id = t.practice_id
             WHERE '.implode(' AND ', $where).'
             ORDER BY p.slug ASC',
            $params,
            $statuses !== [] ? ['statuses' => \Doctrine\DBAL\ArrayParameterType::STRING] : []
        );
    }

    public function sourcePayload(array $source): array
    {
        return [
            'slug' => (string) ($source['slug'] ?? $source['source_slug'] ?? ''),
            'designation' => (string) ($source['designation'] ?? ''),
            'designationShort' => $source['designation_short'] ?? null,
            'h1Title' => $source['h1_title'] ?? null,
            'introduction' => $source['introduction'] ?? null,
            'introductionShort' => $source['introduction_short'] ?? null,
            'description' => $source['description'] ?? null,
            'descriptionShort' => $source['description_short'] ?? null,
            'tags' => $this->decodeJson($source['tags'] ?? null),
        ];
    }

    public function normalizePayload(array $sourcePayload, array $payload, string $locale): array
    {
        $normalized = [];
        foreach (self::FIELDS as $field) {
            $normalized[$field] = $payload[$field] ?? null;
        }
        $normalized['slug'] = $this->normalizeSlug((string) $normalized['slug']);
        if ($normalized['slug'] === '') {
            $normalized['slug'] = $this->normalizeSlug((string) ($sourcePayload['slug'] ?? ''));
        }
        $normalized['designation'] = trim((string) ($normalized['designation'] ?: ($sourcePayload['designation'] ?? '')));

        return $normalized;
    }

    public function validatePayload(array $sourcePayload, array $payload): void
    {
        foreach (self::FIELDS as $field) {
            if (!array_key_exists($field, $payload)) {
                throw new AiTranslationValidationException(sprintf('Missing translated field "%s".', $field));
            }
        }
        if (!preg_match('/^[a-z0-9][a-z0-9\-]*$/', (string) $payload['slug'])) {
            throw new AiTranslationValidationException('Translated slug is invalid.');
        }
        if (trim((string) $payload['designation']) === '') {
            throw new AiTranslationValidationException('Translated designation is required.');
        }
        $this->assertSameJsonShape($sourcePayload['tags'] ?? null, $payload['tags'] ?? null, 'tags');
        foreach (['introduction', 'introductionShort', 'description', 'descriptionShort'] as $field) {
            $this->assertPlaceholdersPreserved((string) ($sourcePayload[$field] ?? ''), (string) ($payload[$field] ?? ''), $field);
        }
    }

    public function assertSlugAvailable(array $source, string $locale, array $payload): void
    {
        $existing = $this->connection->fetchAssociative('SELECT practice_id FROM practice_translation WHERE locale = :locale AND slug = :slug', ['locale' => $locale, 'slug' => $payload['slug']]);
        if (is_array($existing) && (int) $existing['practice_id'] !== (int) $source['source_id']) {
            throw new AiTranslationConflictException(sprintf('CONFLICT: practice slug "%s" already exists for locale "%s".', $payload['slug'], $locale));
        }
    }

    public function persistTarget(array $source, string $locale, array $payload, string $sourceHash): void
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $values = [
            'locale' => $locale,
            'designation' => (string) $payload['designation'],
            'slug' => (string) $payload['slug'],
            'designation_short' => $payload['designationShort'],
            'h1_title' => $payload['h1Title'],
            'introduction' => $payload['introduction'],
            'introduction_short' => $payload['introductionShort'],
            'description' => $payload['description'],
            'description_short' => $payload['descriptionShort'],
            'tags' => $this->encodeJson($payload['tags']),
            'translation_status' => 'ai_translated',
            'source_content_hash' => $sourceHash,
            'source_updated_at' => $now,
            'updated_at' => $now,
        ];
        $target = $this->findTarget((int) $source['source_id'], $locale);
        if ($target === null) {
            $values['practice_id'] = (int) $source['source_id'];
            $values['created_at'] = $now;
            $this->connection->insert('practice_translation', $values);
            return;
        }
        $this->connection->update('practice_translation', $values, ['id' => (int) $target['id']]);
    }

    public function exportRow(array $target): array
    {
        return [
            'source' => ['entity' => 'Practice', 'slug' => $target['source_slug']],
            'locale' => $target['locale'],
            'slug' => $target['slug'],
            'designation' => $target['designation'],
            'designationShort' => $target['designation_short'],
            'h1Title' => $target['h1_title'],
            'introduction' => $target['introduction'],
            'introductionShort' => $target['introduction_short'],
            'description' => $target['description'],
            'descriptionShort' => $target['description_short'],
            'tags' => $this->decodeJson($target['tags'] ?? null),
            'status' => $target['translation_status'],
            'sourceContentHash' => $target['source_content_hash'],
        ];
    }

    public function importRows(string $locale, array $rows, bool $dryRun, bool $overwrite): array
    {
        $counts = ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'conflict' => 0, 'rows' => 0, 'dryRun' => $dryRun, 'conflicts' => []];
        foreach ($rows as $row) {
            ++$counts['rows'];
            $sourceSlug = (string) ($row['source']['slug'] ?? '');
            $source = $this->connection->fetchAssociative('SELECT id source_id, slug source_slug FROM practice WHERE slug = :slug', ['slug' => $sourceSlug]);
            if (!is_array($source) || ($row['locale'] ?? null) !== $locale) {
                ++$counts['conflict'];
                $counts['conflicts'][] = 'CONFLICT source/locale: '.$sourceSlug;
                continue;
            }
            $payload = ['slug'=>$row['slug'],'designation'=>$row['designation'],'designationShort'=>$row['designationShort'] ?? null,'h1Title'=>$row['h1Title'] ?? null,'introduction'=>$row['introduction'] ?? null,'introductionShort'=>$row['introductionShort'] ?? null,'description'=>$row['description'] ?? null,'descriptionShort'=>$row['descriptionShort'] ?? null,'tags'=>$row['tags'] ?? null];
            try {
                $this->validatePayload($payload, $payload);
                $this->assertSlugAvailable($source, $locale, $payload);
            } catch (\Throwable $exception) {
                ++$counts['conflict'];
                $counts['conflicts'][] = $exception->getMessage();
                continue;
            }
            $target = $this->findTarget((int) $source['source_id'], $locale);
            if ($target === null) {
                ++$counts['created'];
                if (!$dryRun) {
                    $this->persistImported($source, $locale, $row);
                }
                continue;
            }
            $existing = $this->exportRow(array_merge($target, ['source_slug' => $sourceSlug]));
            if ($this->rowsEqual($existing, $row)) {
                ++$counts['unchanged'];
                continue;
            }
            if (!$overwrite) {
                ++$counts['conflict'];
                $counts['conflicts'][] = 'CONFLICT existing differs: '.$sourceSlug;
                continue;
            }
            ++$counts['updated'];
            if (!$dryRun) {
                $this->persistImported($source, $locale, $row);
            }
        }

        return $counts;
    }

    private function persistImported(array $source, string $locale, array $row): void
    {
        $this->persistTarget($source, $locale, [
            'slug' => $row['slug'],
            'designation' => $row['designation'],
            'designationShort' => $row['designationShort'] ?? null,
            'h1Title' => $row['h1Title'] ?? null,
            'introduction' => $row['introduction'] ?? null,
            'introductionShort' => $row['introductionShort'] ?? null,
            'description' => $row['description'] ?? null,
            'descriptionShort' => $row['descriptionShort'] ?? null,
            'tags' => $row['tags'] ?? null,
        ], (string) ($row['sourceContentHash'] ?? ''));
        $target = $this->findTarget((int) $source['source_id'], $locale);
        if (is_array($target)) {
            $this->connection->update('practice_translation', [
                'translation_status' => (string) $row['status'],
                'source_content_hash' => $row['sourceContentHash'] ?? null,
                'updated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ], ['id' => (int) $target['id']]);
        }
    }
}
