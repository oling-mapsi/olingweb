<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260227193000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create home_card table to select hero insight cards';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('home_card')) {
            return;
        }
        $this->addSql('CREATE TABLE home_card (id INT AUTO_INCREMENT NOT NULL, service_id INT DEFAULT NULL, projet_id INT DEFAULT NULL, practice_id INT DEFAULT NULL, metier_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_3A9A622AED5CA9E6 (service_id), INDEX IDX_3A9A622ADE7D5E3F (projet_id), INDEX IDX_3A9A622A9BB6C6B3 (practice_id), INDEX IDX_3A9A622ABC9F09CC (metier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE home_card ADD CONSTRAINT FK_3A9A622AED5CA9E6 FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE home_card ADD CONSTRAINT FK_3A9A622ADE7D5E3F FOREIGN KEY (projet_id) REFERENCES projet (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE home_card ADD CONSTRAINT FK_3A9A622A9BB6C6B3 FOREIGN KEY (practice_id) REFERENCES practice (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE home_card ADD CONSTRAINT FK_3A9A622ABC9F09CC FOREIGN KEY (metier_id) REFERENCES metier (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE home_card');
    }
}
