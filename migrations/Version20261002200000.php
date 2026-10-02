<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261002200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add locale metadata to ERP questionnaire submissions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE erp_questionnaire_submission ADD locale VARCHAR(5) DEFAULT 'fr' NOT NULL, ADD questionnaire_version VARCHAR(16) DEFAULT 'v1' NOT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE erp_questionnaire_submission DROP locale, DROP questionnaire_version');
    }
}
