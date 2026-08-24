<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260824143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add SEO referential metadata to projects for XLSX import and public rendering';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() !== 'mysql') {
            return;
        }

        $this->addSql('ALTER TABLE projet ADD external_id VARCHAR(32) DEFAULT NULL, ADD client_name VARCHAR(255) DEFAULT NULL, ADD territory VARCHAR(255) DEFAULT NULL, ADD period_label VARCHAR(255) DEFAULT NULL, ADD public_url VARCHAR(255) DEFAULT NULL, ADD short_description LONGTEXT DEFAULT NULL, ADD proof_status VARCHAR(255) DEFAULT NULL, ADD publication_status VARCHAR(255) DEFAULT NULL, ADD software_tags JSON DEFAULT NULL, ADD software_families JSON DEFAULT NULL, ADD software_relation VARCHAR(255) DEFAULT NULL, ADD software_priority VARCHAR(64) DEFAULT NULL, ADD historical_reference TINYINT(1) DEFAULT 0 NOT NULL, ADD metadata JSON DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_50159CAAFD79AEB ON projet (external_id)');
    }

    public function down(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() !== 'mysql') {
            return;
        }

        $this->addSql('DROP INDEX UNIQ_50159CAAFD79AEB ON projet');
        $this->addSql('ALTER TABLE projet DROP external_id, DROP client_name, DROP territory, DROP period_label, DROP public_url, DROP short_description, DROP proof_status, DROP publication_status, DROP software_tags, DROP software_families, DROP software_relation, DROP software_priority, DROP historical_reference, DROP metadata');
    }
}
