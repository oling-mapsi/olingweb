<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260301113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add image_hero to content_item for cropped admin images';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('content_item') && !$schema->getTable('content_item')->hasColumn('image_hero')) {
            $this->addSql('ALTER TABLE content_item ADD image_hero VARCHAR(255) DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('content_item') && $schema->getTable('content_item')->hasColumn('image_hero')) {
            $this->addSql('ALTER TABLE content_item DROP image_hero');
        }
    }
}
