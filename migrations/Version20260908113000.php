<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260908113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add retention purge date to ERP questionnaire submissions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE erp_questionnaire_submission ADD retention_purge_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('UPDATE erp_questionnaire_submission SET retention_purge_at = DATE_ADD(created_at, INTERVAL 180 DAY) WHERE retention_purge_at IS NULL');
        $this->addSql('ALTER TABLE erp_questionnaire_submission CHANGE retention_purge_at retention_purge_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE erp_questionnaire_submission DROP retention_purge_at');
    }
}
