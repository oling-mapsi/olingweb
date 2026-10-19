<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/** Reviewed FR editorial deltas only. No schema changes or external calls. */
final class Version20261019220000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Separate ERP support roles and QSE / ISO 9001; consolidate obsolete internal links.';
    }

    public function up(Schema $schema): void
    {
        $plan = json_decode((string) file_get_contents(__DIR__.'/../data/seo/p1-corrections-20261019.fr.json'), true, 512, JSON_THROW_ON_ERROR);
        $pages = $this->connection->fetchAllAssociative("SELECT t.*, p.slug AS source_slug FROM site_page_translation t JOIN site_page p ON p.id = t.site_page_id WHERE t.locale = 'fr'");
        $services = $this->connection->fetchAllAssociative("SELECT t.*, s.slug AS source_slug FROM service_translation t JOIN services s ON s.id = t.service_id WHERE t.locale = 'fr'");
        $this->prepareUpdates('site_page_translation', $pages, $plan['pages'], 'structured_data');
        $this->prepareUpdates('service_translation', $services, $plan['services'], 'public_narrative');
        // Practice links are data too; preserve every non-link field and all translations.
        $practices = $this->connection->fetchAllAssociative("SELECT * FROM practice_translation WHERE locale = 'fr'");
        $this->prepareUpdates('practice_translation', $practices, [], 'public_narrative');
    }

    private function prepareUpdates(string $table, array $rows, array $patches, string $jsonColumn): void
    {
        $bySlug = array_column($patches, null, 'slug');
        $seen = [];
        foreach ($rows as $row) {
            $original = $row;
            $payload = $this->decode($row[$jsonColumn] ?? null);
            $slug = $row['source_slug'] ?? '';
            if (isset($bySlug[$slug])) {
                $patch = $bySlug[$slug];
                $seen[$slug] = true;
                foreach ($patch['fields'] as $key => $after) {
                    $this->guard($row[$key] ?? null, $patch['expected_fields'][$key], $after, $slug.'.'.$key);
                    $row[$key] = $after;
                }
                if ($jsonColumn === 'structured_data') {
                    foreach ($patch['payloads'] as $key => $values) {
                        $payload[$key] = $this->mergePatch($payload[$key] ?? [], $values, $patch['expected_payloads'][$key], $slug.'.'.$key);
                    }
                } else {
                    $payload = $this->mergePatch($payload, $patch['narrative'], $patch['expected_narrative'], $slug);
                }
            }
            $payload = $this->rewriteLinks($payload);
            if ($payload !== $this->decode($original[$jsonColumn] ?? null)) {
                $row[$jsonColumn] = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            foreach (['body_html', 'hero_side_html', 'description', 'description_short', 'introduction', 'introduction_short'] as $column) {
                if (is_string($row[$column] ?? null)) {
                    $row[$column] = $this->rewriteLinks($row[$column]);
                }
            }
            $changes = [];
            foreach ($row as $column => $value) {
                if ($column !== 'source_slug' && $value !== $original[$column]) {
                    $changes[$column] = $value;
                }
            }
            if ($changes !== []) {
                $assignments = array_map(static fn (string $column): string => $column.' = ?', array_keys($changes));
                $this->addSql('UPDATE '.$table.' SET '.implode(', ', $assignments).' WHERE id = ? AND locale = ?', [...array_values($changes), $row['id'], 'fr']);
            }
        }
        $this->abortIf(array_diff(array_keys($bySlug), array_keys($seen)) !== [], 'Missing reviewed French content rows; inspect the current CMS before applying this lot.');
    }

    private function mergePatch(array $current, array $patch, array $expected, string $path): array
    {
        foreach ($patch as $key => $after) {
            $this->guard($current[$key] ?? null, $expected[$key] ?? null, $after, $path.'.'.$key);
            $current[$key] = $after;
        }

        return $current;
    }

    private function guard(mixed $current, mixed $expected, mixed $after, string $path): void
    {
        $this->abortIf($this->normalized($current) !== $this->normalized($expected) && $this->normalized($current) !== $this->normalized($after), 'Editorial drift at '.$path.'; review instead of overwriting CMS edits.');
    }

    private function normalized(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }
        if (!array_is_list($value)) {
            ksort($value);
        }

        return array_map(fn (mixed $child): mixed => $this->normalized($child), $value);
    }

    private function decode(mixed $value): array
    {
        return is_array($value) ? $value : json_decode($value ?: '{}', true, 512, JSON_THROW_ON_ERROR);
    }

    private function rewriteLinks(mixed $value): mixed
    {
        if (is_array($value)) {
            if (isset($value['href']) && is_string($value['href'])) {
                $value['href'] = preg_replace('~^/practice/consulting(?=[?#]|$)~', '/amoa-si', $value['href']);
                if (($value['href'] ?? null) === '/erp-progiciel' && in_array(trim($value['label'] ?? ''), ['ERP', 'ERP/progiciel', 'AMOA ERP', 'AMOA ERP et progiciels'], true)) {
                    $value['href'] = '/business-apps/erp';
                }
            }

            return array_map(fn (mixed $child): mixed => $this->rewriteLinks($child), $value);
        }
        if (!is_string($value)) {
            return $value;
        }
        $value = preg_replace('~(href=["\'])(?:https://oling\.fr)?/practice/consulting(?=["\'?#])~', '$1/amoa-si', $value);

        return preg_replace('~(<a\b[^>]*href=["\'])/erp-progiciel(["\'][^>]*>\s*)(ERP|ERP/progiciel|AMOA ERP|AMOA ERP et progiciels)(\s*</a>)~u', '$1/business-apps/erp$2$3$4', $value);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Restore only the reviewed FR fields from the pre-application editorial snapshot; do not overwrite subsequent CMS edits.');
    }
}
