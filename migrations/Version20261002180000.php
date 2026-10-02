<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261002180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add translated public profile payload to team translations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE team_translation ADD public_profile JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE team_translation DROP public_profile');
    }
}
