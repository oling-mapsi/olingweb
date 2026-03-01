<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260301101500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create legal pages table for RGPD, sécurité, mentions légales';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('legal_page')) {
            $this->addSql('CREATE TABLE legal_page (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(190) NOT NULL, title VARCHAR(255) DEFAULT NULL, body LONGTEXT DEFAULT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_19E4D2CB989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('legal_page')) {
            $this->addSql('DROP TABLE legal_page');
        }
    }
}
