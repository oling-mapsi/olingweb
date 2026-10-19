<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261014120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add editorial hero media controls to site pages.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_page ADD hero_media_mode VARCHAR(16) DEFAULT NULL, ADD hero_image_caption LONGTEXT DEFAULT NULL, ADD hero_image_focal_point VARCHAR(32) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_page DROP hero_media_mode, DROP hero_image_caption, DROP hero_image_focal_point');
    }
}
