<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261013143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add analytics conversion attribution table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE analytics_conversion_attribution (id INT AUTO_INCREMENT NOT NULL, conversion_type VARCHAR(32) NOT NULL, conversion_reference VARCHAR(80) NOT NULL, session_id VARCHAR(64) NOT NULL, entry_page VARCHAR(255) NOT NULL, conversion_page VARCHAR(255) NOT NULL, source VARCHAR(64) NOT NULL, visited_pages JSON NOT NULL COMMENT \'(DC2Type:json)\', converted_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_analytics_conversion_converted_at (converted_at), INDEX idx_analytics_conversion_session (session_id), INDEX idx_analytics_conversion_entry_source (entry_page, source), UNIQUE INDEX uniq_analytics_conversion_reference (conversion_type, conversion_reference), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE analytics_conversion_attribution');
    }
}
