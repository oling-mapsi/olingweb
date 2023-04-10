<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230409143344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE services ADD introduction_short LONGTEXT DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL, ADD description_short LONGTEXT DEFAULT NULL, ADD designation_short VARCHAR(255) DEFAULT NULL, ADD ico VARCHAR(255) DEFAULT NULL, ADD image1 VARCHAR(255) NOT NULL, ADD image2 VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE services DROP introduction_short, DROP description, DROP description_short, DROP designation_short, DROP ico, DROP image1, DROP image2');
    }
}
