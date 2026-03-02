<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260302114000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add home project KPI items and seed defaults';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('home_project_kpi')) {
            $this->addSql('CREATE TABLE home_project_kpi (id INT AUTO_INCREMENT NOT NULL, indicator VARCHAR(50) NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, position INT DEFAULT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        $this->addSql("INSERT INTO home_project_kpi (indicator, title, description, position, is_active)
            SELECT '+200', 'Projets pilotés', 'AMOA, transformation SI et conformité menés de bout en bout.', 1, 1
            WHERE NOT EXISTS (SELECT 1 FROM home_project_kpi WHERE title = 'Projets pilotés')");
        $this->addSql("INSERT INTO home_project_kpi (indicator, title, description, position, is_active)
            SELECT '95%', 'Taux de succès', 'Projets sécurisés et cadrés par notre méthodologie ISO 27001.', 2, 1
            WHERE NOT EXISTS (SELECT 1 FROM home_project_kpi WHERE title = 'Taux de succès')");
        $this->addSql("INSERT INTO home_project_kpi (indicator, title, description, position, is_active)
            SELECT '12', 'Secteurs couverts', 'Public, finance, industrie, santé et services essentiels.', 3, 1
            WHERE NOT EXISTS (SELECT 1 FROM home_project_kpi WHERE title = 'Secteurs couverts')");
        $this->addSql("INSERT INTO home_project_kpi (indicator, title, description, position, is_active)
            SELECT '+5 000', 'Collaborateurs couverts', 'Agents et salariés protégés par nos dispositifs DPO.', 4, 1
            WHERE NOT EXISTS (SELECT 1 FROM home_project_kpi WHERE title = 'Collaborateurs couverts')");
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('home_project_kpi')) {
            $this->addSql('DROP TABLE home_project_kpi');
        }
    }
}
