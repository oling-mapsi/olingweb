<?php

namespace App\Service\I18n;

interface EntityTranslationAdapterInterface
{
    public function entityName(): string;

    /**
     * @return list<array<string, mixed>>
     */
    public function selectSources(?int $id, ?string $slug, int $limit): array;

    /**
     * @return array<string, mixed>|null
     */
    public function findTarget(int $sourceId, string $locale): ?array;

    /**
     * @param string[] $statuses
     * @return list<array<string, mixed>>
     */
    public function selectTargets(string $locale, array $statuses = []): array;

    /**
     * @param array<string, mixed> $source
     * @return array<string, mixed>
     */
    public function sourcePayload(array $source): array;

    /**
     * @param array<string, mixed> $sourcePayload
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function normalizePayload(array $sourcePayload, array $payload, string $locale): array;

    /**
     * @param array<string, mixed> $sourcePayload
     * @param array<string, mixed> $payload
     */
    public function validatePayload(array $sourcePayload, array $payload): void;

    /**
     * @param array<string, mixed> $source
     * @param array<string, mixed> $payload
     */
    public function assertSlugAvailable(array $source, string $locale, array $payload): void;

    /**
     * @param array<string, mixed> $source
     * @param array<string, mixed> $payload
     */
    public function persistTarget(array $source, string $locale, array $payload, string $sourceHash): void;

    /**
     * @return array<string, mixed>
     */
    public function exportRow(array $target): array;

    /**
     * @return array{created:int, updated:int, unchanged:int, conflict:int, rows:int, dryRun:bool, conflicts:string[]}
     */
    public function importRows(string $locale, array $rows, bool $dryRun, bool $overwrite): array;
}
