<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260908113500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align ERP questionnaire public token index name with Doctrine mapping';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE erp_questionnaire_submission RENAME INDEX UNIQ_ERP_QUESTIONNAIRE_TOKEN TO UNIQ_2EFDF520AE981E3B');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE erp_questionnaire_submission RENAME INDEX UNIQ_2EFDF520AE981E3B TO UNIQ_ERP_QUESTIONNAIRE_TOKEN');
    }
}
