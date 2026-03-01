<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260228000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add featured projects selection fields';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('projet') && !$schema->getTable('projet')->hasColumn('featured_projects')) {
            $this->addSql('ALTER TABLE projet ADD featured_projects TINYINT(1) DEFAULT 0 NOT NULL');
        }
        if ($schema->hasTable('projet') && !$schema->getTable('projet')->hasColumn('featured_projects_rank')) {
            $this->addSql('ALTER TABLE projet ADD featured_projects_rank INT DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('projet') && $schema->getTable('projet')->hasColumn('featured_projects')) {
            $this->addSql('ALTER TABLE projet DROP featured_projects');
        }
        if ($schema->hasTable('projet') && $schema->getTable('projet')->hasColumn('featured_projects_rank')) {
            $this->addSql('ALTER TABLE projet DROP featured_projects_rank');
        }
    }
}
