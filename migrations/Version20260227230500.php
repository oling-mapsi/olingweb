<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260227230500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add CTA fields to home_section for hero management';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('home_section')) {
            return;
        }
        $table = $schema->getTable('home_section');
        $columns = [];
        if (!$table->hasColumn('cta_label')) {
            $columns[] = 'ADD cta_label VARCHAR(255) DEFAULT NULL';
        }
        if (!$table->hasColumn('cta_url')) {
            $columns[] = 'ADD cta_url VARCHAR(255) DEFAULT NULL';
        }
        if (!$table->hasColumn('cta_label_secondary')) {
            $columns[] = 'ADD cta_label_secondary VARCHAR(255) DEFAULT NULL';
        }
        if (!$table->hasColumn('cta_url_secondary')) {
            $columns[] = 'ADD cta_url_secondary VARCHAR(255) DEFAULT NULL';
        }
        if ($columns) {
            $this->addSql('ALTER TABLE home_section ' . implode(', ', $columns));
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE home_section DROP cta_label, DROP cta_url, DROP cta_label_secondary, DROP cta_url_secondary');
    }
}
