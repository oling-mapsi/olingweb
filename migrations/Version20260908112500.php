<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260908112500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add internal commercial scoring to ERP questionnaire submissions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE erp_questionnaire_submission ADD scoring JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE erp_questionnaire_submission DROP scoring');
    }
}
