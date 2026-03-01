<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260227251500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add tags (hashtags) to practice';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('practice') && !$schema->getTable('practice')->hasColumn('tags')) {
            $this->addSql('ALTER TABLE practice ADD tags JSON DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('practice') && $schema->getTable('practice')->hasColumn('tags')) {
            $this->addSql('ALTER TABLE practice DROP tags');
        }
    }
}
