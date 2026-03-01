<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260227000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add app_user table and default admin account';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('app_user')) {
            return;
        }
        $this->addSql('CREATE TABLE app_user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_88BDF0E9F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('INSERT INTO app_user (username, roles, password) VALUES (\'flo\', \'[\"ROLE_ADMIN\"]\', \'$2y$10$r14JcPHwXSq8YXbCI0yumOyUTqFCwCB7L0OTaIL1XiViFCgJLQOkO\')');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE app_user');
    }
}
