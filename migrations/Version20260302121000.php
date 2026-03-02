<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260302121000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add project to team many-to-many relation';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('projet_team')) {
            $this->addSql('CREATE TABLE projet_team (projet_id INT NOT NULL, team_id INT NOT NULL, INDEX IDX_5D82CBBEC18272 (projet_id), INDEX IDX_5D82CBBE296CD8AE (team_id), PRIMARY KEY(projet_id, team_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE projet_team ADD CONSTRAINT FK_5D82CBBEC18272 FOREIGN KEY (projet_id) REFERENCES projet (id) ON DELETE CASCADE');
            $this->addSql('ALTER TABLE projet_team ADD CONSTRAINT FK_5D82CBBE296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('projet_team')) {
            $this->addSql('ALTER TABLE projet_team DROP FOREIGN KEY FK_5D82CBBEC18272');
            $this->addSql('ALTER TABLE projet_team DROP FOREIGN KEY FK_5D82CBBE296CD8AE');
            $this->addSql('DROP TABLE projet_team');
        }
    }
}
