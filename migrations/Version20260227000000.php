<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260227000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create content_item table for backoffice content management';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('content_item')) {
            return;
        }
        $this->addSql("CREATE TABLE content_item (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, body LONGTEXT DEFAULT NULL, image_path VARCHAR(255) DEFAULT NULL, icon_path VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE content_item');
    }
}
