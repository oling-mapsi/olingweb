<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260825103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create public chat knowledge index table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE chat_public_document (id INT AUTO_INCREMENT NOT NULL, source_type VARCHAR(32) NOT NULL, source_entity_id INT DEFAULT NULL, safe_title VARCHAR(255) NOT NULL, safe_text LONGTEXT NOT NULL, url VARCHAR(255) NOT NULL, section_title VARCHAR(255) DEFAULT NULL, keywords JSON NOT NULL, search_text LONGTEXT NOT NULL, is_active TINYINT(1) NOT NULL, is_confidential_reference TINYINT(1) NOT NULL, checksum VARCHAR(64) NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', image VARCHAR(255) DEFAULT NULL, INDEX idx_chat_public_document_source (source_type, source_entity_id), INDEX idx_chat_public_document_active (is_active, updated_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE chat_public_document');
    }
}
