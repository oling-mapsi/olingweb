<?php

namespace App\Service\I18n;

use App\Entity\SitePageTranslation;
use Symfony\Component\HttpKernel\KernelInterface;

final class EntityTranslationSnapshotService
{
    /** @var array<string, EntityTranslationAdapterInterface> */
    private array $adapters = [];

    public function __construct(
        ServiceTranslationAdapter $serviceAdapter,
        PracticeTranslationAdapter $practiceAdapter,
        ProjectTranslationAdapter $projectAdapter,
        TeamTranslationAdapter $teamAdapter,
        private readonly KernelInterface $kernel,
    ) {
        foreach ([$serviceAdapter, $practiceAdapter, $projectAdapter, $teamAdapter] as $adapter) {
            $this->adapters[$adapter->entityName()] = $adapter;
        }
    }

    public function supports(string $entity): bool
    {
        return isset($this->adapters[$this->normalizeEntity($entity)]);
    }

    /**
     * @param string[] $statuses
     * @return array<string, mixed>
     */
    public function export(string $entity, string $locale, array $statuses = []): array
    {
        SitePageTranslation::assertSupportedLocale($locale);
        $adapter = $this->adapter($entity);

        return [
            'entity' => ucfirst($adapter->entityName()).'Translation',
            'locale' => $locale,
            'exportedAt' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'translations' => array_map(fn (array $row): array => $adapter->exportRow($row), $adapter->selectTargets($locale, $statuses)),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function writeExport(string $entity, array $payload, ?string $path = null): string
    {
        $path ??= $this->defaultPath($entity, (string) ($payload['locale'] ?? ''));
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException(sprintf('Cannot create export directory "%s".', $directory));
        }
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n");

        return $path;
    }

    /**
     * @return array{created:int, updated:int, unchanged:int, conflict:int, rows:int, dryRun:bool, conflicts:string[]}
     */
    public function import(string $entity, string $locale, ?string $path = null, bool $dryRun = false, bool $overwrite = false): array
    {
        $adapter = $this->adapter($entity);
        $path ??= $this->defaultPath($entity, $locale);
        $payload = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($payload) || ($payload['locale'] ?? null) !== $locale || !is_array($payload['translations'] ?? null)) {
            throw new \RuntimeException(sprintf('Invalid translation export "%s".', $path));
        }

        return $adapter->importRows($locale, $payload['translations'], $dryRun, $overwrite);
    }

    public function defaultPath(string $entity, string $locale): string
    {
        $entityName = $this->adapter($entity)->entityName();
        $name = match ($entityName) {
            'service' => 'services',
            'practice' => 'practices',
            'project' => 'projects',
            'team' => 'team',
            default => $entityName,
        };

        return $this->kernel->getProjectDir().'/data/i18n/reviewed/'.$name.'.'.$locale.'.json';
    }

    private function adapter(string $entity): EntityTranslationAdapterInterface
    {
        $key = $this->normalizeEntity($entity);
        if (!isset($this->adapters[$key])) {
            throw new \InvalidArgumentException(sprintf('Unsupported translation entity "%s".', $entity));
        }

        return $this->adapters[$key];
    }

    private function normalizeEntity(string $entity): string
    {
        $entity = strtolower(str_replace(['_', '-'], '', $entity));

        return match ($entity) {
            'service', 'services' => 'service',
            'practice', 'practices' => 'practice',
            'project', 'projects', 'projet', 'projets' => 'project',
            'team', 'teams' => 'team',
            default => $entity,
        };
    }
}
