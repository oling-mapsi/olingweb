<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260301090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add hero image fields for services, projets, and metiers';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('services') && !$schema->getTable('services')->hasColumn('image_hero')) {
            $this->addSql('ALTER TABLE services ADD image_hero VARCHAR(255) DEFAULT NULL');
        }
        if ($schema->hasTable('projet') && !$schema->getTable('projet')->hasColumn('image_hero')) {
            $this->addSql('ALTER TABLE projet ADD image_hero VARCHAR(255) DEFAULT NULL');
        }
        if ($schema->hasTable('metier') && !$schema->getTable('metier')->hasColumn('image_hero')) {
            $this->addSql('ALTER TABLE metier ADD image_hero VARCHAR(255) DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('services') && $schema->getTable('services')->hasColumn('image_hero')) {
            $this->addSql('ALTER TABLE services DROP image_hero');
        }
        if ($schema->hasTable('projet') && $schema->getTable('projet')->hasColumn('image_hero')) {
            $this->addSql('ALTER TABLE projet DROP image_hero');
        }
        if ($schema->hasTable('metier') && $schema->getTable('metier')->hasColumn('image_hero')) {
            $this->addSql('ALTER TABLE metier DROP image_hero');
        }
    }
}
