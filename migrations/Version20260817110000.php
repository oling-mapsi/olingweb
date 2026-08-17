<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260817110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix lingering ISO 27001 typo in dynamic services content';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();
        if ($platform !== 'mysql') {
            return;
        }

        $this->connection->executeStatement(
            "UPDATE services SET designation = REPLACE(designation, 'IS027001', 'ISO 27001') WHERE slug = 'si' AND designation LIKE '%IS027001%'"
        );
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();
        if ($platform !== 'mysql') {
            return;
        }

        $this->connection->executeStatement(
            "UPDATE services SET designation = REPLACE(designation, 'ISO 27001', 'IS027001') WHERE slug = 'si' AND designation LIKE '%ISO 27001%'"
        );
    }
}
