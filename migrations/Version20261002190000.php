<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261002190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add public service narrative payload to service translations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service_translation ADD public_narrative JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service_translation DROP public_narrative');
    }
}
