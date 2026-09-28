<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260928160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add minimal chat response observability';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chat_message ADD provider VARCHAR(32) DEFAULT NULL, ADD model VARCHAR(64) DEFAULT NULL, ADD fallback_used TINYINT(1) NOT NULL DEFAULT 0, ADD owner_url VARCHAR(255) DEFAULT NULL, ADD selected_documents JSON DEFAULT NULL, ADD latency_ms INT DEFAULT NULL, ADD input_tokens INT DEFAULT NULL, ADD output_tokens INT DEFAULT NULL, ADD error_code VARCHAR(128) DEFAULT NULL, ADD request_id VARCHAR(128) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chat_message DROP provider, DROP model, DROP fallback_used, DROP owner_url, DROP selected_documents, DROP latency_ms, DROP input_tokens, DROP output_tokens, DROP error_code, DROP request_id');
    }
}
