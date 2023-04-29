<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230429003146 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE projet (id INT AUTO_INCREMENT NOT NULL, designation VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE projet_services (projet_id INT NOT NULL, services_id INT NOT NULL, INDEX IDX_54B28A9EC18272 (projet_id), INDEX IDX_54B28A9EAEF5A6C1 (services_id), PRIMARY KEY(projet_id, services_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE projet_services ADD CONSTRAINT FK_54B28A9EC18272 FOREIGN KEY (projet_id) REFERENCES projet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE projet_services ADD CONSTRAINT FK_54B28A9EAEF5A6C1 FOREIGN KEY (services_id) REFERENCES services (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE projet_services DROP FOREIGN KEY FK_54B28A9EC18272');
        $this->addSql('ALTER TABLE projet_services DROP FOREIGN KEY FK_54B28A9EAEF5A6C1');
        $this->addSql('DROP TABLE projet');
        $this->addSql('DROP TABLE projet_services');
    }
}
