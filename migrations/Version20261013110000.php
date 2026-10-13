<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261013110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add first-party analytics events';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('analytics_event')) {
            return;
        }

        $this->addSql('CREATE TABLE analytics_event (
            id INT AUTO_INCREMENT NOT NULL,
            event_id VARCHAR(64) NOT NULL,
            session_id VARCHAR(64) NOT NULL,
            event_name VARCHAR(64) NOT NULL,
            page_path VARCHAR(255) NOT NULL,
            previous_page_path VARCHAR(255) DEFAULT NULL,
            source VARCHAR(64) NOT NULL,
            utm_source VARCHAR(100) DEFAULT NULL,
            utm_medium VARCHAR(100) DEFAULT NULL,
            utm_campaign VARCHAR(150) DEFAULT NULL,
            locale VARCHAR(16) DEFAULT NULL,
            occurred_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX uniq_analytics_event_event_id (event_id),
            INDEX idx_analytics_event_occurred_at (occurred_at),
            INDEX idx_analytics_event_session_time (session_id, occurred_at),
            INDEX idx_analytics_event_name_time (event_name, occurred_at),
            INDEX idx_analytics_event_page_time (page_path, occurred_at),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('analytics_event')) {
            $this->addSql('DROP TABLE analytics_event');
        }
    }
}
