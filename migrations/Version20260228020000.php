<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260228020000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add home projects/awards sections and awards items seed';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('home_section') && !$schema->getTable('home_section')->hasColumn('eyebrow')) {
            $this->addSql('ALTER TABLE home_section ADD eyebrow VARCHAR(255) DEFAULT NULL');
        }

        if (!$schema->hasTable('home_award_item')) {
            $this->addSql('CREATE TABLE home_award_item (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, subtitle VARCHAR(255) DEFAULT NULL, body LONGTEXT DEFAULT NULL, position INT DEFAULT NULL, is_open TINYINT(1) DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        $this->addSql("INSERT INTO home_section (slug, title, intro, eyebrow, updated_at)
            SELECT 'projects', 'Chaque jour, nous nous emparons du changement et générons des résultats concrets.', 'Votre allié tactique et organisationnel pour mener à bien vos initiatives stratégiques.', NULL, NOW()
            WHERE NOT EXISTS (SELECT 1 FROM home_section WHERE slug = 'projects')");

        $this->addSql("INSERT INTO home_section (slug, title, intro, eyebrow, updated_at)
            SELECT 'awards', 'La confiance se construit sur des preuves concrètes.', NULL, 'Prix et reconnaissance internationale', NOW()
            WHERE NOT EXISTS (SELECT 1 FROM home_section WHERE slug = 'awards')");

        $this->addSql("INSERT INTO home_award_item (title, subtitle, body, position, is_open)
            SELECT 'Une gouvernance d\\'entreprise éthique', 'Cadre ISO 27001, pilotage responsable et traçabilité renforcée.', 'OLING structure les programmes de conformité et de sécurité avec des méthodes de pilotage exigeantes, centrées sur la valeur et la conformité.', 1, 1
            WHERE NOT EXISTS (SELECT 1 FROM home_award_item WHERE title = 'Une gouvernance d\\'entreprise éthique')");
        $this->addSql("INSERT INTO home_award_item (title, subtitle, body, position, is_open)
            SELECT 'Un cabinet de confiance', 'DPO certifié, audits réguliers, gouvernance RGPD.', 'Nos experts accompagnent des organisations publiques et privées avec un niveau de preuve continu : documentation, audits, indicateurs et plans d’action.', 2, 0
            WHERE NOT EXISTS (SELECT 1 FROM home_award_item WHERE title = 'Un cabinet de confiance')");
        $this->addSql("INSERT INTO home_award_item (title, subtitle, body, position, is_open)
            SELECT 'Un pilotage outillé', 'MAPSI industrialise les audits et la conformité.', 'MAPSI centralise les plans d’action, incidents, registres et reportings pour une traçabilité complète et partagée.', 3, 0
            WHERE NOT EXISTS (SELECT 1 FROM home_award_item WHERE title = 'Un pilotage outillé')");
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('home_award_item')) {
            $this->addSql('DROP TABLE home_award_item');
        }
        if ($schema->hasTable('home_section') && $schema->getTable('home_section')->hasColumn('eyebrow')) {
            $this->addSql('ALTER TABLE home_section DROP eyebrow');
        }
        $this->addSql("DELETE FROM home_section WHERE slug IN ('projects', 'awards')");
    }
}
