<?php

namespace App\Service\I18n;

use Doctrine\DBAL\ArrayParameterType;

final class TeamTranslationAdapter extends AbstractDbTranslationAdapter
{
    private const FIELDS = ['titre', 'shortcv', 'publicProfile'];

    public function entityName(): string
    {
        return 'team';
    }

    public function selectSources(?int $id, ?string $slug, int $limit): array
    {
        $where = ['fr.locale = :source_locale', 'fr.translation_status = :source_status'];
        $params = ['source_locale' => 'fr', 'source_status' => 'published', 'limit' => $limit];
        if ($id !== null) {
            $where[] = 't.id = :id';
            $params['id'] = $id;
        }
        if ($slug !== null && $slug !== '') {
            $where[] = 'LOWER(REPLACE(t.noncomplet, " ", "-")) = :slug';
            $params['slug'] = mb_strtolower($slug);
        }

        return $this->connection->fetchAllAssociative(
            'SELECT t.id source_id, LOWER(REPLACE(t.noncomplet, " ", "-")) source_slug, t.noncomplet source_name, fr.*
             FROM team t
             INNER JOIN team_translation fr ON fr.team_id = t.id
             WHERE '.implode(' AND ', $where).'
             ORDER BY t.id ASC
             LIMIT :limit',
            $params,
            ['limit' => \PDO::PARAM_INT]
        );
    }

    public function findTarget(int $sourceId, string $locale): ?array
    {
        $row = $this->connection->fetchAssociative('SELECT * FROM team_translation WHERE team_id = :id AND locale = :locale', ['id' => $sourceId, 'locale' => $locale]);

        return is_array($row) ? $row : null;
    }

    public function selectTargets(string $locale, array $statuses = []): array
    {
        $where = ['tt.locale = :locale'];
        $params = ['locale' => $locale];
        $types = [];
        if ($statuses !== []) {
            $where[] = 'tt.translation_status IN (:statuses)';
            $params['statuses'] = $statuses;
            $types['statuses'] = ArrayParameterType::STRING;
        }

        return $this->connection->fetchAllAssociative(
            'SELECT LOWER(REPLACE(t.noncomplet, " ", "-")) source_slug, t.noncomplet source_name, tt.*
             FROM team_translation tt
             INNER JOIN team t ON t.id = tt.team_id
             WHERE '.implode(' AND ', $where).'
             ORDER BY t.id ASC',
            $params,
            $types
        );
    }

    public function sourcePayload(array $source): array
    {
        return [
            'titre' => $source['titre'] ?? null,
            'shortcv' => $source['shortcv'] ?? null,
            'publicProfile' => $this->decodeJson($source['public_profile'] ?? null),
        ];
    }

    public function normalizePayload(array $sourcePayload, array $payload, string $locale): array
    {
        $normalized = [];
        foreach (self::FIELDS as $field) {
            $normalized[$field] = $payload[$field] ?? null;
        }
        if (($sourcePayload['publicProfile']['slug'] ?? null) && is_array($normalized['publicProfile'] ?? null)) {
            $normalized['publicProfile']['slug'] = $sourcePayload['publicProfile']['slug'];
        }
        foreach (['displayName', 'linkedin', 'photo', 'publicationsUrl', 'relationSchema'] as $field) {
            if (($sourcePayload['publicProfile'][$field] ?? null) && is_array($normalized['publicProfile'] ?? null)) {
                $normalized['publicProfile'][$field] = $sourcePayload['publicProfile'][$field];
            }
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
        $this->assertSameJsonShape($sourcePayload['publicProfile'] ?? null, $payload['publicProfile'] ?? null, 'publicProfile');
        $this->assertPlaceholdersPreserved((string) ($sourcePayload['shortcv'] ?? ''), (string) ($payload['shortcv'] ?? ''), 'shortcv');
        foreach (['slug', 'displayName', 'linkedin', 'photo', 'publicationsUrl', 'relationSchema'] as $field) {
            if (($sourcePayload['publicProfile'][$field] ?? null) !== ($payload['publicProfile'][$field] ?? null)) {
                throw new AiTranslationValidationException(sprintf('Non-linguistic team profile field "%s" must not change.', $field));
            }
        }
    }

    public function assertSlugAvailable(array $source, string $locale, array $payload): void
    {
        unset($source, $locale, $payload);
    }

    public function persistTarget(array $source, string $locale, array $payload, string $sourceHash): void
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $values = [
            'locale' => $locale,
            'titre' => $payload['titre'],
            'shortcv' => $payload['shortcv'],
            'public_profile' => $this->encodeJson($payload['publicProfile']),
            'translation_status' => 'ai_translated',
            'source_content_hash' => $sourceHash,
            'source_updated_at' => $now,
            'updated_at' => $now,
        ];
        $target = $this->findTarget((int) $source['source_id'], $locale);
        if ($target === null) {
            $values['team_id'] = (int) $source['source_id'];
            $values['created_at'] = $now;
            $this->connection->insert('team_translation', $values);
            return;
        }
        $this->connection->update('team_translation', $values, ['id' => (int) $target['id']]);
    }

    public function exportRow(array $target): array
    {
        return [
            'source' => ['entity' => 'Team', 'slug' => $target['source_slug'], 'name' => $target['source_name'] ?? null],
            'locale' => $target['locale'],
            'titre' => $target['titre'],
            'shortcv' => $target['shortcv'],
            'publicProfile' => $this->decodeJson($target['public_profile'] ?? null),
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
            $source = $this->connection->fetchAssociative('SELECT id source_id, LOWER(REPLACE(noncomplet, " ", "-")) source_slug, noncomplet source_name FROM team WHERE LOWER(REPLACE(noncomplet, " ", "-")) = :slug', ['slug' => $sourceSlug]);
            if (!is_array($source) || ($row['locale'] ?? null) !== $locale) {
                ++$counts['conflict'];
                $counts['conflicts'][] = 'CONFLICT source/locale: '.$sourceSlug;
                continue;
            }
            $payload = ['titre' => $row['titre'] ?? null, 'shortcv' => $row['shortcv'] ?? null, 'publicProfile' => $row['publicProfile'] ?? null];
            try {
                $this->validatePayload($payload, $payload);
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
            $existing = $this->exportRow(array_merge($target, ['source_slug' => $sourceSlug, 'source_name' => $source['source_name'] ?? null]));
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
            'titre' => $row['titre'] ?? null,
            'shortcv' => $row['shortcv'] ?? null,
            'publicProfile' => $row['publicProfile'] ?? null,
        ], (string) ($row['sourceContentHash'] ?? ''));
        $target = $this->findTarget((int) $source['source_id'], $locale);
        if (is_array($target)) {
            $this->connection->update('team_translation', [
                'translation_status' => (string) $row['status'],
                'source_content_hash' => $row['sourceContentHash'] ?? null,
                'updated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ], ['id' => (int) $target['id']]);
        }
    }
}
