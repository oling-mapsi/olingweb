<?php

namespace App\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\AbortMigration;
use DoctrineMigrations\Version20261019220000 as Migration;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

require_once __DIR__.'/../migrations/Version20261019220000.php';

/** Opt-in integration test: a private local socket and a disposable database only. */
final class SeoP1MySqlIdempotenceTest extends TestCase
{
    private Connection $db;
    private Connection $admin;
    private string $database;
    private const TABLES = ['site_page_translation', 'service_translation', 'practice_translation'];

    protected function setUp(): void
    {
        $socket = getenv('OLING_P1_MYSQL_SOCKET');
        $baseline = getenv('OLING_P1_BASELINE_FILE');
        if (!$socket || !$baseline) {
            self::markTestSkipped('Set OLING_P1_MYSQL_SOCKET and OLING_P1_BASELINE_FILE for disposable MySQL 8.4 tests.');
        }
        if (!str_starts_with((string) realpath(dirname($socket)), '/private/tmp/oling-p1-mysql84-')) {
            throw new \RuntimeException('Refusing any socket outside the private local recipe directory.');
        }
        $params = ['driver' => 'pdo_mysql', 'unix_socket' => $socket, 'user' => 'root', 'password' => '', 'charset' => 'utf8mb4'];
        $this->admin = DriverManager::getConnection($params);
        self::assertStringStartsWith('8.4.', $this->admin->fetchOne('SELECT VERSION()'));
        $this->database = 'oling_p1_recipe_'.bin2hex(random_bytes(6));
        $this->admin->executeStatement('CREATE DATABASE '.$this->database.' CHARACTER SET utf8mb4');
        $this->db = DriverManager::getConnection([...$params, 'dbname' => $this->database]);
        $source = json_decode(file_get_contents($baseline), true, 512, JSON_THROW_ON_ERROR);
        foreach (['site_page', 'services'] as $table) {
            $this->db->executeStatement('CREATE TABLE '.$table.' (id INT PRIMARY KEY, slug VARCHAR(255))');
            foreach ($source['tables'][$table] as $row) {
                $this->db->insert($table, ['id' => $row['id'], 'slug' => $row['slug']]);
            }
        }
        foreach (self::TABLES as $table) {
            $columns = $source['schema'][$table];
            $ddl = array_map(static fn ($col) => '`'.$col.'` '.($col === 'id' ? 'INT PRIMARY KEY' : (self::isJsonColumn($col) ? 'JSON NULL' : 'LONGTEXT NULL')), $columns);
            $this->db->executeStatement('CREATE TABLE '.$table.' ('.implode(', ', $ddl).')');
            foreach ($source['tables'][$table] as $row) {
                foreach ($row as $col => $value) {
                    if (is_array($value)) {
                        $row[$col] = json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    }
                }
                $this->db->insert($table, $row);
            }
        }
    }

    protected function tearDown(): void
    {
        if (isset($this->db)) {
            $this->db->close();
        }
        if (isset($this->database, $this->admin) && str_starts_with($this->database, 'oling_p1_recipe_')) {
            $this->admin->executeStatement('DROP DATABASE '.$this->database);
            $this->admin->close();
        }
    }

    private static function isJsonColumn(string $column): bool
    {
        return in_array($column, ['structured_data', 'public_narrative'], true);
    }

    private static function equivalent(string $column, mixed $a, mixed $b): bool
    {
        return Migration::valuesAreEquivalent(self::isJsonColumn($column) ? 'json' : 'scalar',
            self::isJsonColumn($column) ? json_decode($a ?? '{}', true, 512, JSON_THROW_ON_ERROR) : $a,
            self::isJsonColumn($column) ? json_decode($b ?? '{}', true, 512, JSON_THROW_ON_ERROR) : $b);
    }

    private function snapshot(): array
    {
        $all = [];
        foreach (self::TABLES as $table) {
            $all[$table] = $this->db->fetchAllAssociative('SELECT * FROM '.$table.' ORDER BY id');
        }

        return $all;
    }

    private function assertSnapshotEquivalent(array $expected): void
    {
        $actual = $this->snapshot();
        foreach ($expected as $table => $rows) {
            self::assertCount(count($rows), $actual[$table]);
            foreach ($rows as $i => $row) {
                foreach ($row as $col => $value) {
                    self::assertTrue(self::equivalent($col, $value, $actual[$table][$i][$col]), $table.'.'.$row['id'].'.'.$col);
                }
            }
        }
    }

    private function plan(string $class = Migration::class): array
    {
        $migration = new $class($this->db, new NullLogger());
        $migration->up(new Schema());

        return $migration->getSql();
    }

    private function backup(array $queries): array
    {
        $backup = [];
        foreach ($queries as $q) {
            self::assertSame(1, preg_match('/^UPDATE (site_page_translation|service_translation) SET (.+) WHERE id = \? AND locale = \?$/', $q->getStatement(), $match));
            $params = $q->getParameters();
            $id = $params[count($params) - 2];
            $columns = array_map(static fn ($x) => explode(' = ', $x)[0], explode(', ', $match[2]));
            $row = $this->db->fetchAssociative('SELECT * FROM '.$match[1].' WHERE id = ? AND locale = ?', [$id, 'fr']);
            $before = $after = [];
            foreach ($columns as $i => $col) {
                $before[$col] = $row[$col];
                $after[$col] = $params[$i];
            }
            $backup[] = ['table' => $match[1], 'id' => $id, 'locale' => 'fr', 'fields' => $columns, 'before' => $before, 'after' => $after];
        }

        return $backup;
    }

    private function execute(array $queries): void
    {
        $this->db->transactional(function () use ($queries): void {
            foreach ($queries as $q) {
                self::assertSame(1, $this->db->executeStatement($q->getStatement(), $q->getParameters(), $q->getTypes()));
            }
        });
    }

    /** Both rollback drift guards and field comparisons share the migration comparator. */
    private function restore(array $backup): int
    {
        return $this->db->transactional(function () use ($backup): int {
            foreach ($backup as $b) {
                $row = $this->db->fetchAssociative('SELECT * FROM '.$b['table'].' WHERE id = ? AND locale = ? FOR UPDATE', [$b['id'], 'fr']);
                foreach ($b['fields'] as $col) {
                    if (!self::equivalent($col, $row[$col], $b['after'][$col]) && !self::equivalent($col, $row[$col], $b['before'][$col])) {
                        throw new \RuntimeException('UNEXPECTED CMS DRIFT during targeted restore');
                    }
                }
            }
            $restored = 0;
            foreach ($backup as $b) {
                $restored += $this->db->update($b['table'], $b['before'], ['id' => $b['id'], 'locale' => 'fr']);
            }

            return $restored;
        });
    }

    public function testRealMySqlCanonicalizationThreePassesDriftAndTargetedRestore(): void
    {
        $baseline = $this->snapshot();
        $this->db->executeStatement('CREATE TABLE json_canonicalization_probe (id INT PRIMARY KEY, payload JSON)');
        $input = '{"zz":1,"a":{"long":true,"b":2}}';
        $this->db->insert('json_canonicalization_probe', ['id' => 1, 'payload' => $input]);
        $output = $this->db->fetchOne('SELECT payload FROM json_canonicalization_probe WHERE id = 1');
        self::assertNotSame($input, $output);
        self::assertSame(['a', 'zz'], array_keys(json_decode($output, true)));
        self::assertSame(['b', 'long'], array_keys(json_decode($output, true)['a']));
        self::assertTrue(Migration::valuesAreEquivalent('json', json_decode($input, true), json_decode($output, true)));

        $oldResult = null;
        if ($originalFile = getenv('OLING_P1_ORIGINAL_MIGRATION')) {
            require_once $originalFile;
            $old = $this->plan(\DoctrineMigrations\BeforeJsonFix::class);
            self::assertCount(17, $old);
            $oldBackup = $this->backup($old);
            $this->execute($old);
            $secondOld = $this->plan(\DoctrineMigrations\BeforeJsonFix::class);
            self::assertCount(2, $secondOld);
            $ids = array_map(static fn ($q) => $q->getParameters()[count($q->getParameters()) - 2], $secondOld);
            sort($ids);
            self::assertSame([6, 11], $ids);
            foreach ($secondOld as $q) {
                $params = $q->getParameters();
                $stored = $this->db->fetchOne('SELECT structured_data FROM site_page_translation WHERE id = ?', [$params[1]]);
                self::assertTrue(self::equivalent('structured_data', $stored, $params[0]));
            }
            self::assertSame(17, $this->restore($oldBackup));
            $this->assertSnapshotEquivalent($baseline);
            $oldResult = ['first' => count($old), 'second' => count($secondOld), 'redundant_row_ids' => $ids];
        }

        $first = $this->plan();
        self::assertCount(17, $first);
        $backup = $this->backup($first);
        $this->execute($first);
        // Each call performs fresh SELECTs from MySQL, not from in-memory target values.
        $second = $this->plan();
        $third = $this->plan();
        self::assertSame([], $second);
        self::assertSame([], $third);

        $title = $this->db->fetchOne('SELECT hero_title FROM site_page_translation WHERE id = 6');
        $this->db->update('site_page_translation', ['hero_title' => 'UNEXPECTED CMS TEXT EDIT'], ['id' => 6]);
        $textDrift = $this->snapshot();
        try {
            $this->plan();
            self::fail('A real text change must abort.');
        } catch (AbortMigration $error) {
            self::assertStringContainsString('Editorial drift', $error->getMessage());
        }
        self::assertSame($textDrift, $this->snapshot());
        $this->db->update('site_page_translation', ['hero_title' => $title], ['id' => 6]);

        $json = $this->db->fetchOne('SELECT structured_data FROM site_page_translation WHERE id = 11');
        $corrupt = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $corrupt['landingNarrative']['citablePassages'][1]['text'] = 'UNEXPECTED CMS JSON EDIT';
        $this->db->update('site_page_translation', ['structured_data' => json_encode($corrupt, JSON_THROW_ON_ERROR)], ['id' => 11]);
        $jsonDrift = $this->snapshot();
        try {
            $this->plan();
            self::fail('A real JSON change must abort.');
        } catch (AbortMigration $error) {
            self::assertStringContainsString('Editorial drift', $error->getMessage());
        }
        self::assertSame($jsonDrift, $this->snapshot());
        try {
            $this->restore($backup);
            self::fail('Restore must reject a subsequent CMS edit.');
        } catch (\RuntimeException $error) {
            self::assertStringContainsString('UNEXPECTED CMS DRIFT', $error->getMessage());
        }
        self::assertSame($jsonDrift, $this->snapshot());
        $this->db->update('site_page_translation', ['structured_data' => $json], ['id' => 11]);
        self::assertSame([], $this->plan());
        self::assertSame(17, $this->restore($backup));
        $this->assertSnapshotEquivalent($baseline);

        if ($proofFile = getenv('OLING_P1_MYSQL_PROOF_FILE')) {
            file_put_contents($proofFile, json_encode([
                'version' => $this->db->fetchOne('SELECT VERSION()'), 'database' => $this->database,
                'baseline_sha256' => hash_file('sha256', getenv('OLING_P1_BASELINE_FILE')),
                'canonicalization' => ['input' => $input, 'stored' => $output, 'semantically_equal' => true],
                'original_candidate' => $oldResult,
                'fixed_candidate' => ['first' => count($first), 'second' => count($second), 'third' => count($third)],
                'text_drift_rejected' => true, 'json_drift_rejected' => true, 'restore_drift_rejected' => true,
                'targeted_backup_rows' => count($backup), 'restore_baseline_all_translations' => true,
                'translation_row_counts' => array_map('count', $baseline), 'backup' => $backup,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        }
    }
}
