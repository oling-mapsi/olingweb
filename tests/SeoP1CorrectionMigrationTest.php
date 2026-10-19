<?php

namespace App\Tests;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\AbortMigration;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

require_once __DIR__.'/../migrations/Version20261019220000.php';

final class SeoP1CorrectionMigrationTest extends TestCase
{
    private function database(): \Doctrine\DBAL\Connection
    {
        $db = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $plan = json_decode(file_get_contents(dirname(__DIR__).'/data/seo/p1-corrections-20261019.fr.json'), true, 512, JSON_THROW_ON_ERROR);
        $db->executeStatement('CREATE TABLE site_page (id INTEGER PRIMARY KEY, slug TEXT)');
        $db->executeStatement('CREATE TABLE services (id INTEGER PRIMARY KEY, slug TEXT)');
        $db->executeStatement('CREATE TABLE practice_translation (id INTEGER PRIMARY KEY, locale TEXT, public_narrative TEXT)');
        foreach ([['pages', 'site_page', 'site_page_translation', 'site_page_id', 'structured_data'], ['services', 'services', 'service_translation', 'service_id', 'public_narrative']] as [$key, $parent, $table, $foreign, $json]) {
            $columns = [];
            foreach ($plan[$key] as $patch) {
                $columns = array_unique([...$columns, ...array_keys($patch['fields'])]);
            }
            $definitions = implode('', array_map(fn ($name) => ', '.$name.' TEXT', $columns));
            $db->executeStatement('CREATE TABLE '.$table.' (id INTEGER PRIMARY KEY, '.$foreign.' INTEGER, locale TEXT, '.$json.' TEXT'.$definitions.')');
            foreach ($plan[$key] as $index => $patch) {
                $id = $index + 1;
                $db->insert($parent, ['id' => $id, 'slug' => $patch['slug']]);
                $payload = $key === 'pages' ? $patch['expected_payloads'] : $patch['expected_narrative'];
                $payload['unrelatedEditorialProof'] = ['text' => 'Must survive', 'approved' => true];
                $db->insert($table, ['id' => $id, $foreign => $id, 'locale' => 'fr', $json => json_encode($payload), ...$patch['expected_fields']]);
                $db->insert($table, ['id' => $id + 100, $foreign => $id, 'locale' => 'en', $json => '{"title":"Unchanged English content"}']);
            }
        }
        $db->insert('site_page', ['id' => 999, 'slug' => 'gmao']);
        $db->insert('site_page_translation', ['id' => 999, 'site_page_id' => 999, 'locale' => 'fr', 'structured_data' => '{"landingNarrative":{"metaTitle":"AMOA GMAO","heroTitle":"AMOA GMAO"}}']);

        return $db;
    }

    public function testEditorialDeltasPreserveUnrelatedFieldsAndTranslationsAndAreIdempotent(): void
    {
        $db = $this->database();
        $protected = $db->fetchAssociative('SELECT * FROM site_page_translation WHERE id = 999');
        $english = $db->fetchAllAssociative("SELECT * FROM site_page_translation WHERE locale = 'en'");
        $migration = new \DoctrineMigrations\Version20261019220000($db, new NullLogger());
        $migration->up(new Schema());
        self::assertNotEmpty($migration->getSql());
        foreach ($migration->getSql() as $query) {
            $db->executeStatement($query->getStatement(), $query->getParameters(), $query->getTypes());
        }
        self::assertSame($protected, $db->fetchAssociative('SELECT * FROM site_page_translation WHERE id = 999'));
        self::assertSame($english, $db->fetchAllAssociative("SELECT * FROM site_page_translation WHERE locale = 'en'"));
        foreach ($db->fetchAllAssociative("SELECT structured_data FROM site_page_translation WHERE locale = 'fr' AND id <> 999") as $row) {
            self::assertSame(['text' => 'Must survive', 'approved' => true], json_decode($row['structured_data'], true)['unrelatedEditorialProof']);
        }
        $second = new \DoctrineMigrations\Version20261019220000($db, new NullLogger());
        $second->up(new Schema());
        self::assertSame([], $second->getSql());
    }

    public function testEditorialDriftIsRejectedBeforeAnyQueuedSqlRuns(): void
    {
        $db = $this->database();
        $db->update('site_page_translation', ['hero_title' => 'A newer CMS edit'], ['id' => 1]);
        $before = $db->fetchAllAssociative('SELECT * FROM site_page_translation');
        $migration = new \DoctrineMigrations\Version20261019220000($db, new NullLogger());
        try {
            $migration->up(new Schema());
            self::fail('Editorial drift must abort the migration.');
        } catch (AbortMigration $error) {
            self::assertStringContainsString('Editorial drift', $error->getMessage());
        }
        self::assertSame($before, $db->fetchAllAssociative('SELECT * FROM site_page_translation'));
    }

    /** @dataProvider productionJsonRows */
    public function testExactProductionPassageKeyOrderDoesNotQueueAnUpdate(int $id, int $fixtureId, int $passage): void
    {
        $db = $this->database();
        $db->update('site_page_translation', ['id' => $id], ['id' => $fixtureId]);
        $first = new \DoctrineMigrations\Version20261019220000($db, new NullLogger());
        $first->up(new Schema());
        foreach ($first->getSql() as $query) {
            $db->executeStatement($query->getStatement(), $query->getParameters(), $query->getTypes());
        }
        $payload = json_decode($db->fetchOne('SELECT structured_data FROM site_page_translation WHERE id = ?', [$id]), true, 512, JSON_THROW_ON_ERROR);
        $original = $payload['landingNarrative']['citablePassages'][$passage];
        $payload['landingNarrative']['citablePassages'][$passage] = array_reverse($original, true);
        self::assertNotSame(array_keys($original), array_keys($payload['landingNarrative']['citablePassages'][$passage]));
        $db->update('site_page_translation', ['structured_data' => json_encode($payload, JSON_THROW_ON_ERROR)], ['id' => $id]);
        $second = new \DoctrineMigrations\Version20261019220000($db, new NullLogger());
        $second->up(new Schema());
        self::assertSame([], $second->getSql(), 'Key order in production row '.$id.' must not create an UPDATE.');
    }

    public static function productionJsonRows(): iterable
    {
        yield 'quality row 11 passage 1' => [11, 3, 1];
        yield 'ERP row 6 passage 0' => [6, 1, 0];
    }

}
