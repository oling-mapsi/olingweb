<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260817113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Unpublish and desitemap the pilot e2e resource article if it exists';
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
            WHERE slug IN ('ressource-pilot-oling-e2e-article', 'pilot-oling-e2e-article')
               OR external_id IN ('growth-local-pilot-oling-e2e-article', 'pilot-oling-e2e-article')
               OR title LIKE '%Pilot Oling E2E article%'
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
            WHERE slug IN ('ressource-pilot-oling-e2e-article', 'pilot-oling-e2e-article')
               OR external_id IN ('growth-local-pilot-oling-e2e-article', 'pilot-oling-e2e-article')
               OR title LIKE '%Pilot Oling E2E article%'
            SQL
        );
    }
}
