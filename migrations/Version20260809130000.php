<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260809130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add home hero texts to metier';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();
        if ($platform !== 'mysql') {
            return;
        }

        $this->addSql('ALTER TABLE metier ADD home_hero_intro VARCHAR(255) DEFAULT NULL, ADD home_hero_text1 VARCHAR(255) DEFAULT NULL, ADD home_hero_text2 VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();
        if ($platform !== 'mysql') {
            return;
        }

        $this->addSql('ALTER TABLE metier DROP home_hero_intro, DROP home_hero_text1, DROP home_hero_text2');
    }
}
