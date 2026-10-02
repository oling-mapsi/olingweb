<?php

namespace App\Service\I18n;

final class ServiceTranslationAdapter extends AbstractDbTranslationAdapter
{
    private const FIELDS = ['slug', 'designation', 'designationShort', 'introductionShort', 'description', 'descriptionShort', 'publicNarrative'];

    public function entityName(): string
    {
        return 'service';
    }

    public function selectSources(?int $id, ?string $slug, int $limit): array
    {
        $where = ['fr.locale = :source_locale', 'fr.translation_status = :source_status'];
        $params = ['source_locale' => 'fr', 'source_status' => 'published', 'limit' => $limit];
        if ($id !== null) {
            $where[] = 's.id = :id';
            $params['id'] = $id;
        }
        if ($slug !== null && $slug !== '') {
            $where[] = 's.slug = :slug';
            $params['slug'] = $slug;
        }

        return $this->connection->fetchAllAssociative(
            'SELECT s.id source_id, s.slug source_slug, p.slug practice_slug, fr.*
             FROM services s
             INNER JOIN practice p ON p.id = s.practice_id
             INNER JOIN service_translation fr ON fr.service_id = s.id
             WHERE '.implode(' AND ', $where).'
             ORDER BY p.slug ASC, s.slug ASC
             LIMIT :limit',
            $params,
            ['limit' => \PDO::PARAM_INT]
        );
    }

    public function findTarget(int $sourceId, string $locale): ?array
    {
        $row = $this->connection->fetchAssociative('SELECT * FROM service_translation WHERE service_id = :id AND locale = :locale', ['id' => $sourceId, 'locale' => $locale]);

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
            'SELECT s.slug source_slug, p.slug practice_slug, t.*
             FROM service_translation t
             INNER JOIN services s ON s.id = t.service_id
             INNER JOIN practice p ON p.id = s.practice_id
             WHERE '.implode(' AND ', $where).'
             ORDER BY p.slug ASC, s.slug ASC',
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
            'introductionShort' => $source['introduction_short'] ?? null,
            'description' => $source['description'] ?? null,
            'descriptionShort' => $source['description_short'] ?? null,
            'publicNarrative' => $this->decodeJson($source['public_narrative'] ?? null),
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
        $this->assertSameJsonShape($sourcePayload['publicNarrative'] ?? null, $payload['publicNarrative'] ?? null, 'publicNarrative');
        foreach (['introductionShort', 'description', 'descriptionShort'] as $field) {
            $this->assertPlaceholdersPreserved((string) ($sourcePayload[$field] ?? ''), (string) ($payload[$field] ?? ''), $field);
        }
    }

    public function assertSlugAvailable(array $source, string $locale, array $payload): void
    {
        $existing = $this->connection->fetchAssociative('SELECT service_id FROM service_translation WHERE locale = :locale AND slug = :slug', ['locale' => $locale, 'slug' => $payload['slug']]);
        if (is_array($existing) && (int) $existing['service_id'] !== (int) $source['source_id']) {
            throw new AiTranslationConflictException(sprintf('CONFLICT: service slug "%s" already exists for locale "%s".', $payload['slug'], $locale));
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
            'introduction_short' => $payload['introductionShort'],
            'description' => $payload['description'],
            'description_short' => $payload['descriptionShort'],
            'public_narrative' => $this->encodeJson($payload['publicNarrative']),
            'translation_status' => 'ai_translated',
            'source_content_hash' => $sourceHash,
            'source_updated_at' => $now,
            'updated_at' => $now,
        ];
        $target = $this->findTarget((int) $source['source_id'], $locale);
        if ($target === null) {
            $values['service_id'] = (int) $source['source_id'];
            $values['created_at'] = $now;
            $this->connection->insert('service_translation', $values);
            return;
        }
        $this->connection->update('service_translation', $values, ['id' => (int) $target['id']]);
    }

    public function exportRow(array $target): array
    {
        return [
            'source' => ['entity' => 'Service', 'slug' => $target['source_slug'], 'practiceSlug' => $target['practice_slug'] ?? null],
            'locale' => $target['locale'],
            'slug' => $target['slug'],
            'designation' => $target['designation'],
            'designationShort' => $target['designation_short'],
            'introductionShort' => $target['introduction_short'],
            'description' => $target['description'],
            'descriptionShort' => $target['description_short'],
            'publicNarrative' => $this->decodeJson($target['public_narrative'] ?? null),
            'status' => $target['translation_status'],
            'sourceContentHash' => $target['source_content_hash'],
        ];
    }

    public function importRows(string $locale, array $rows, bool $dryRun, bool $overwrite): array
    {
        return $this->importDbRows('services', 'service_translation', 'service_id', $locale, $rows, $dryRun, $overwrite);
    }

    private function importDbRows(string $sourceTable, string $translationTable, string $ownerColumn, string $locale, array $rows, bool $dryRun, bool $overwrite): array
    {
        $counts = ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'conflict' => 0, 'rows' => 0, 'dryRun' => $dryRun, 'conflicts' => []];
        foreach ($rows as $row) {
            ++$counts['rows'];
            $sourceSlug = (string) ($row['source']['slug'] ?? '');
            $source = $this->connection->fetchAssociative("SELECT id source_id, slug source_slug FROM $sourceTable WHERE slug = :slug", ['slug' => $sourceSlug]);
            if (!is_array($source) || ($row['locale'] ?? null) !== $locale) {
                ++$counts['conflict'];
                $counts['conflicts'][] = 'CONFLICT source/locale: '.$sourceSlug;
                continue;
            }
            $payload = ['slug'=>$row['slug'],'designation'=>$row['designation'],'designationShort'=>$row['designationShort'] ?? null,'introductionShort'=>$row['introductionShort'] ?? null,'description'=>$row['description'] ?? null,'descriptionShort'=>$row['descriptionShort'] ?? null,'publicNarrative'=>$row['publicNarrative'] ?? null];
            try {
                $this->validatePayload($payload, $payload);
                $this->assertSlugAvailable($source, $locale, $payload);
            } catch (\Throwable $exception) {
                ++$counts['conflict'];
                $counts['conflicts'][] = $exception->getMessage();
                continue;
            }
            $target = $this->findTarget((int) $source['source_id'], $locale);
            $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
            if ($target === null) {
                ++$counts['created'];
                if (!$dryRun) {
                    $this->persistImported($source, $locale, $row, $now);
                }
                continue;
            }
            $existing = $this->exportRow(array_merge($target, ['source_slug' => $sourceSlug, 'practice_slug' => $row['source']['practiceSlug'] ?? null]));
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
                $this->persistImported($source, $locale, $row, $now);
            }
        }

        return $counts;
    }

    private function persistImported(array $source, string $locale, array $row, string $now): void
    {
        $this->persistTarget($source, $locale, [
            'slug' => $row['slug'],
            'designation' => $row['designation'],
            'designationShort' => $row['designationShort'] ?? null,
            'introductionShort' => $row['introductionShort'] ?? null,
            'description' => $row['description'] ?? null,
            'descriptionShort' => $row['descriptionShort'] ?? null,
            'publicNarrative' => $row['publicNarrative'] ?? null,
        ], (string) ($row['sourceContentHash'] ?? ''));
        $target = $this->findTarget((int) $source['source_id'], $locale);
        if (is_array($target)) {
            $this->connection->update('service_translation', [
                'translation_status' => (string) $row['status'],
                'source_content_hash' => $row['sourceContentHash'] ?? null,
                'updated_at' => $now,
            ], ['id' => (int) $target['id']]);
        }
    }
}
