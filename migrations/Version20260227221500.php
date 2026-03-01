<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260227221500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add featured_home flag to practice for homepage cards';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('practice') && $schema->getTable('practice')->hasColumn('featured_home')) {
            return;
        }
        $this->addSql('ALTER TABLE practice ADD featured_home TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE practice DROP featured_home');
    }
}
