<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260302110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add email to app_user and enforce uniqueness';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_user ADD email VARCHAR(180) DEFAULT NULL');
        $this->addSql('UPDATE app_user SET email = username WHERE email IS NULL');
        $this->addSql('ALTER TABLE app_user MODIFY email VARCHAR(180) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_APP_USER_EMAIL ON app_user (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_APP_USER_EMAIL ON app_user');
        $this->addSql('ALTER TABLE app_user DROP email');
    }
}
