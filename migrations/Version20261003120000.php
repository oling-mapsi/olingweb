<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261003120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add public team publishing and ordering flags';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('team');
        if (!$table->hasColumn('is_public')) {
            $this->addSql('ALTER TABLE team ADD is_public TINYINT(1) DEFAULT 0 NOT NULL');
        }
        if (!$table->hasColumn('display_order')) {
            $this->addSql('ALTER TABLE team ADD display_order INT DEFAULT NULL');
        }

        $this->addSql('UPDATE team SET is_public = 0, display_order = NULL');

        foreach ([
            1 => ['Florestan Rouet'],
            2 => ['Dorothée Maitrias', 'Dorothee Maitrias'],
            3 => ['Manuel Feuillard'],
            4 => ['Hanna Badan', 'Hanna BADAN'],
            5 => ['Julien Pujol'],
            6 => ['Claire Tillion', 'Claire Tillon'],
            7 => ['Jean-Claude Vati', 'Jean Claude VATI', 'Jean Claude Vati'],
        ] as $order => $names) {
            $quotedNames = implode(', ', array_map(fn (string $name): string => $this->connection->quote($name), $names));
            $this->addSql(sprintf('UPDATE team SET is_public = 1, display_order = %d WHERE noncomplet IN (%s)', $order, $quotedNames));
        }
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('team');
        if ($table->hasColumn('display_order')) {
            $this->addSql('ALTER TABLE team DROP display_order');
        }
        if ($table->hasColumn('is_public')) {
            $this->addSql('ALTER TABLE team DROP is_public');
        }
    }
}
