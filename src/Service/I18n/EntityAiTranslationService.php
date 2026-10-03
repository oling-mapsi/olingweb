<?php

namespace App\Service\I18n;

use Symfony\Component\HttpKernel\KernelInterface;

final class EntityAiTranslationService
{
    /** @var array<string, EntityTranslationAdapterInterface> */
    private array $adapters = [];

    public function __construct(
        ServiceTranslationAdapter $serviceAdapter,
        PracticeTranslationAdapter $practiceAdapter,
        ProjectTranslationAdapter $projectAdapter,
        TeamTranslationAdapter $teamAdapter,
        private readonly AiTranslationProviderInterface $provider,
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
     * @return list<array<string, mixed>>
     */
    public function selectSources(string $entity, ?int $id, ?string $slug, int $limit): array
    {
        return $this->adapter($entity)->selectSources($id, $slug, $limit);
    }

    /**
     * @return array{entity:string,id:int,slug:string,locale:string,fields:list<string>,currentStatus:?string,outdated:bool,action:string}
     */
    public function plan(string $entity, array $source, string $locale, bool $overwrite = false, bool $onlyMissing = false, bool $onlyOutdated = false): array
    {
        $adapter = $this->adapter($entity);
        $target = $adapter->findTarget((int) $source['source_id'], $locale);
        $sourceHash = $this->hash($adapter->sourcePayload($source));
        $outdated = is_array($target) && ($target['source_content_hash'] ?? null) !== $sourceHash;
        $action = 'generate';

        if (is_array($target) && !$overwrite) {
            $action = $outdated && $onlyOutdated ? 'regenerate_outdated' : 'skip_exists';
        }
        if ($onlyMissing && is_array($target)) {
            $action = 'skip_exists';
        }
        if ($onlyOutdated && (!is_array($target) || !$outdated)) {
            $action = 'skip_not_outdated';
        }
        if (is_array($target) && $overwrite) {
            $action = $outdated ? 'regenerate_outdated' : 'overwrite';
        }

        return [
            'entity' => $adapter->entityName(),
            'id' => (int) $source['source_id'],
            'slug' => (string) $source['source_slug'],
            'locale' => $locale,
            'fields' => array_keys($adapter->sourcePayload($source)),
            'currentStatus' => is_array($target) ? ($target['translation_status'] ?? null) : null,
            'outdated' => $outdated,
            'action' => $action,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function translate(string $entity, array $source, string $locale, bool $overwrite = false, bool $onlyMissing = false, bool $onlyOutdated = false): array
    {
        $plan = $this->plan($entity, $source, $locale, $overwrite, $onlyMissing, $onlyOutdated);
        if (str_starts_with($plan['action'], 'skip')) {
            throw new \RuntimeException(sprintf('Skipped %s #%d: %s.', $plan['entity'], $plan['id'], $plan['action']));
        }

        $adapter = $this->adapter($entity);
        $sourcePayload = $adapter->sourcePayload($source);
        $payload = $adapter->normalizePayload($sourcePayload, $this->provider->translate($sourcePayload, $locale, $this->glossary())->payload, $locale);
        $adapter->validatePayload($sourcePayload, $payload);
        try {
            $adapter->assertSlugAvailable($source, $locale, $payload);
        } catch (AiTranslationConflictException) {
            $payload['slug'] = trim((string) $payload['slug'], '-').'-'.trim((string) $source['source_slug'], '-');
            $adapter->assertSlugAvailable($source, $locale, $payload);
        }
        $adapter->persistTarget($source, $locale, $payload, $this->hash($sourcePayload));

        return $payload;
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

    /**
     * @param array<string, mixed> $payload
     */
    private function hash(array $payload): string
    {
        ksort($payload);

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    /**
     * @return array<string, mixed>
     */
    private function glossary(): array
    {
        $raw = @file_get_contents($this->kernel->getProjectDir().'/data/i18n/glossary.json');
        if ($raw === false) {
            return [];
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}
