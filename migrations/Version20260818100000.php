<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260818100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Unpublish technical resource articles with pilot/test/demo/e2e slugs';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();
        if ($platform !== 'mysql') {
            return;
        }

        $this->connection->executeStatement(
            <<<'SQL'
            UPDATE site_page
            SET publication_status = 'unpublished',
                unpublished_at = COALESCE(unpublished_at, NOW())
            WHERE slug LIKE 'ressource-pilot-%'
               OR slug LIKE 'ressource-test-%'
               OR slug LIKE 'ressource-demo-%'
               OR slug LIKE 'ressource-e2e-%'
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();
        if ($platform !== 'mysql') {
            return;
        }

        $this->connection->executeStatement(
            <<<'SQL'
            UPDATE site_page
            SET publication_status = 'published',
                unpublished_at = NULL
            WHERE slug LIKE 'ressource-pilot-%'
               OR slug LIKE 'ressource-test-%'
               OR slug LIKE 'ressource-demo-%'
               OR slug LIKE 'ressource-e2e-%'
            SQL
        );
    }
}
