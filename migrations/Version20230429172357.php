<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230429172357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE services_team (services_id INT NOT NULL, team_id INT NOT NULL, INDEX IDX_40D9FEAEAEF5A6C1 (services_id), INDEX IDX_40D9FEAE296CD8AE (team_id), PRIMARY KEY(services_id, team_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE services_team ADD CONSTRAINT FK_40D9FEAEAEF5A6C1 FOREIGN KEY (services_id) REFERENCES services (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE services_team ADD CONSTRAINT FK_40D9FEAE296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE services_team DROP FOREIGN KEY FK_40D9FEAEAEF5A6C1');
        $this->addSql('ALTER TABLE services_team DROP FOREIGN KEY FK_40D9FEAE296CD8AE');
        $this->addSql('DROP TABLE services_team');
    }
}
