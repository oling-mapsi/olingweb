<?php

namespace App\Service\I18n;

use Doctrine\DBAL\ArrayParameterType;

final class ProjectTranslationAdapter extends AbstractDbTranslationAdapter
{
    private const FIELDS = ['slug', 'designation', 'description', 'shortDescription', 'clientName', 'territory', 'periodLabel'];

    public function entityName(): string
    {
        return 'project';
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
             FROM projet p
             INNER JOIN projet_translation fr ON fr.projet_id = p.id
             WHERE '.implode(' AND ', $where).'
             ORDER BY p.featured_projects DESC, p.featured_projects_rank ASC, p.id DESC
             LIMIT :limit',
            $params,
            ['limit' => \PDO::PARAM_INT]
        );
    }

    public function findTarget(int $sourceId, string $locale): ?array
    {
        $row = $this->connection->fetchAssociative('SELECT * FROM projet_translation WHERE projet_id = :id AND locale = :locale', ['id' => $sourceId, 'locale' => $locale]);

        return is_array($row) ? $row : null;
    }

    public function selectTargets(string $locale, array $statuses = []): array
    {
        $where = ['t.locale = :locale'];
        $params = ['locale' => $locale];
        $types = [];
        if ($statuses !== []) {
            $where[] = 't.translation_status IN (:statuses)';
            $params['statuses'] = $statuses;
            $types['statuses'] = ArrayParameterType::STRING;
        }

        return $this->connection->fetchAllAssociative(
            'SELECT p.slug source_slug, t.*
             FROM projet_translation t
             INNER JOIN projet p ON p.id = t.projet_id
             WHERE '.implode(' AND ', $where).'
             ORDER BY p.featured_projects DESC, p.featured_projects_rank ASC, p.id DESC',
            $params,
            $types
        );
    }

    public function sourcePayload(array $source): array
    {
        return [
            'slug' => (string) ($source['slug'] ?? $source['source_slug'] ?? ''),
            'designation' => (string) ($source['designation'] ?? ''),
            'description' => $source['description'] ?? null,
            'shortDescription' => $source['short_description'] ?? null,
            'clientName' => $source['client_name'] ?? null,
            'territory' => $source['territory'] ?? null,
            'periodLabel' => $source['period_label'] ?? null,
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
        foreach (['clientName', 'territory', 'periodLabel'] as $field) {
            $normalized[$field] = $sourcePayload[$field] ?? null;
        }

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
        foreach (['description', 'shortDescription'] as $field) {
            $this->assertPlaceholdersPreserved((string) ($sourcePayload[$field] ?? ''), (string) ($payload[$field] ?? ''), $field);
        }
        foreach (['clientName', 'territory', 'periodLabel'] as $field) {
            if (($sourcePayload[$field] ?? null) !== ($payload[$field] ?? null)) {
                throw new AiTranslationValidationException(sprintf('Non-linguistic project field "%s" must not change.', $field));
            }
        }
    }

    public function assertSlugAvailable(array $source, string $locale, array $payload): void
    {
        $existing = $this->connection->fetchAssociative('SELECT projet_id FROM projet_translation WHERE locale = :locale AND slug = :slug', ['locale' => $locale, 'slug' => $payload['slug']]);
        if (is_array($existing) && (int) $existing['projet_id'] !== (int) $source['source_id']) {
            throw new AiTranslationConflictException(sprintf('CONFLICT: project slug "%s" already exists for locale "%s".', $payload['slug'], $locale));
        }
    }

    public function persistTarget(array $source, string $locale, array $payload, string $sourceHash): void
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $values = [
            'locale' => $locale,
            'designation' => (string) $payload['designation'],
            'slug' => (string) $payload['slug'],
            'description' => $payload['description'],
            'short_description' => $payload['shortDescription'],
            'client_name' => $payload['clientName'],
            'territory' => $payload['territory'],
            'period_label' => $payload['periodLabel'],
            'translation_status' => 'ai_translated',
            'source_content_hash' => $sourceHash,
            'source_updated_at' => $now,
            'updated_at' => $now,
        ];
        $target = $this->findTarget((int) $source['source_id'], $locale);
        if ($target === null) {
            $values['projet_id'] = (int) $source['source_id'];
            $values['created_at'] = $now;
            $this->connection->insert('projet_translation', $values);
            return;
        }
        $this->connection->update('projet_translation', $values, ['id' => (int) $target['id']]);
    }

    public function exportRow(array $target): array
    {
        return [
            'source' => ['entity' => 'Project', 'slug' => $target['source_slug']],
            'locale' => $target['locale'],
            'slug' => $target['slug'],
            'designation' => $target['designation'],
            'description' => $target['description'],
            'shortDescription' => $target['short_description'],
            'clientName' => $target['client_name'],
            'territory' => $target['territory'],
            'periodLabel' => $target['period_label'],
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
            $source = $this->connection->fetchAssociative('SELECT id source_id, slug source_slug FROM projet WHERE slug = :slug', ['slug' => $sourceSlug]);
            if (!is_array($source) || ($row['locale'] ?? null) !== $locale) {
                ++$counts['conflict'];
                $counts['conflicts'][] = 'CONFLICT source/locale: '.$sourceSlug;
                continue;
            }
            $payload = ['slug' => $row['slug'], 'designation' => $row['designation'], 'description' => $row['description'] ?? null, 'shortDescription' => $row['shortDescription'] ?? null, 'clientName' => $row['clientName'] ?? null, 'territory' => $row['territory'] ?? null, 'periodLabel' => $row['periodLabel'] ?? null];
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
            'description' => $row['description'] ?? null,
            'shortDescription' => $row['shortDescription'] ?? null,
            'clientName' => $row['clientName'] ?? null,
            'territory' => $row['territory'] ?? null,
            'periodLabel' => $row['periodLabel'] ?? null,
        ], (string) ($row['sourceContentHash'] ?? ''));
        $target = $this->findTarget((int) $source['source_id'], $locale);
        if (is_array($target)) {
            $this->connection->update('projet_translation', [
                'translation_status' => (string) $row['status'],
                'source_content_hash' => $row['sourceContentHash'] ?? null,
                'updated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ], ['id' => (int) $target['id']]);
        }
    }
}
