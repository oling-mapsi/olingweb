<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230429171544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE metier_projet DROP FOREIGN KEY FK_59981F23C18272');
        $this->addSql('ALTER TABLE metier_projet DROP FOREIGN KEY FK_59981F23ED16FA20');
        $this->addSql('DROP TABLE metier_projet');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE metier_projet (metier_id INT NOT NULL, projet_id INT NOT NULL, INDEX IDX_59981F23ED16FA20 (metier_id), INDEX IDX_59981F23C18272 (projet_id), PRIMARY KEY(metier_id, projet_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE metier_projet ADD CONSTRAINT FK_59981F23C18272 FOREIGN KEY (projet_id) REFERENCES projet (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE metier_projet ADD CONSTRAINT FK_59981F23ED16FA20 FOREIGN KEY (metier_id) REFERENCES metier (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
