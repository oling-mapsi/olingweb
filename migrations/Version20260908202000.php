<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260908202000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Reserved no-op migration created during OLING Lot 1 preparation.';
    }

    public function up(Schema $schema): void
    {
    }

    public function down(Schema $schema): void
    {
    }
}
