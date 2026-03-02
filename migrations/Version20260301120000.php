<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260301120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create agency table to manage OLING addresses';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('agency')) {
            $this->addSql('CREATE TABLE agency (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(190) NOT NULL, street_line1 VARCHAR(255) NOT NULL, street_line2 VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(20) NOT NULL, city VARCHAR(120) NOT NULL, country VARCHAR(120) NOT NULL, country_code VARCHAR(2) NOT NULL, position INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('agency')) {
            $this->addSql('DROP TABLE agency');
        }
    }
}
