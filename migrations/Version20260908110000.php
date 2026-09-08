<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260908110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create ERP questionnaire submissions table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE erp_questionnaire_submission (id INT AUTO_INCREMENT NOT NULL, public_token VARCHAR(64) NOT NULL, full_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(50) NOT NULL, company VARCHAR(255) NOT NULL, job_title VARCHAR(255) DEFAULT NULL, sector VARCHAR(255) DEFAULT NULL, organization_size VARCHAR(32) DEFAULT NULL, user_count VARCHAR(32) DEFAULT NULL, solution_type VARCHAR(64) DEFAULT NULL, urgency VARCHAR(64) DEFAULT NULL, budget_range VARCHAR(64) DEFAULT NULL, answers JSON NOT NULL, summary JSON NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', emailed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_ERP_QUESTIONNAIRE_TOKEN (public_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE erp_questionnaire_submission');
    }
}
