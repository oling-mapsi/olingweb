<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260227243000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add h1_title to practice for dedicated H1';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('practice') && !$schema->getTable('practice')->hasColumn('h1_title')) {
            $this->addSql('ALTER TABLE practice ADD h1_title VARCHAR(255) DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('practice') && $schema->getTable('practice')->hasColumn('h1_title')) {
            $this->addSql('ALTER TABLE practice DROP h1_title');
        }
    }
}
