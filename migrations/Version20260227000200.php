<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260227000200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add image field to metier';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE metier ADD image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE metier DROP image');
    }
}
