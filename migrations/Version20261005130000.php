<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261005130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add standalone Growth campaign domain';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('growth_campaign')) {
            $table = $schema->createTable('growth_campaign');
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('title', 'string', ['length' => 255]);
            $table->addColumn('status', 'string', ['length' => 32]);
            $table->addColumn('created_by', 'string', ['length' => 180, 'notnull' => false]);
            $table->addColumn('created_at', 'datetime_immutable');
            $table->addColumn('updated_at', 'datetime_immutable');
            $table->setPrimaryKey(['id']);
            $table->addIndex(['status'], 'idx_growth_campaign_status');
        }

        if (!$schema->hasTable('growth_content')) {
            $table = $schema->createTable('growth_content');
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('campaign_id', 'integer');
            $table->addColumn('status', 'string', ['length' => 32]);
            $table->addColumn('title', 'string', ['length' => 255]);
            $table->addColumn('slug', 'string', ['length' => 255]);
            $table->addColumn('excerpt', 'text');
            $table->addColumn('content_html', 'text');
            $table->addColumn('meta_title', 'string', ['length' => 255]);
            $table->addColumn('meta_description', 'text');
            $table->addColumn('featured_image', 'string', ['length' => 255, 'notnull' => false]);
            $table->addColumn('categories', 'json');
            $table->addColumn('tags', 'json');
            $table->addColumn('author_display_name', 'string', ['length' => 255]);
            $table->addColumn('created_at', 'datetime_immutable');
            $table->addColumn('updated_at', 'datetime_immutable');
            $table->setPrimaryKey(['id']);
            $table->addIndex(['campaign_id'], 'idx_growth_content_campaign');
            $table->addIndex(['status'], 'idx_growth_content_status');
            $table->addForeignKeyConstraint('growth_campaign', ['campaign_id'], ['id'], ['onDelete' => 'CASCADE']);
        }

        if (!$schema->hasTable('growth_publication')) {
            $table = $schema->createTable('growth_publication');
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('campaign_id', 'integer');
            $table->addColumn('content_id', 'integer');
            $table->addColumn('destination', 'string', ['length' => 32]);
            $table->addColumn('status', 'string', ['length' => 32]);
            $table->addColumn('external_identifier', 'string', ['length' => 190, 'notnull' => false]);
            $table->addColumn('published_at', 'datetime_immutable', ['notnull' => false]);
            $table->addColumn('last_error', 'text', ['notnull' => false]);
            $table->addColumn('created_at', 'datetime_immutable');
            $table->setPrimaryKey(['id']);
            $table->addIndex(['campaign_id'], 'idx_growth_publication_campaign');
            $table->addIndex(['content_id'], 'idx_growth_publication_content');
            $table->addIndex(['destination', 'status'], 'idx_growth_publication_destination_status');
            $table->addForeignKeyConstraint('growth_campaign', ['campaign_id'], ['id'], ['onDelete' => 'CASCADE']);
            $table->addForeignKeyConstraint('growth_content', ['content_id'], ['id'], ['onDelete' => 'CASCADE']);
        }

        if (!$schema->hasTable('growth_audit_event')) {
            $table = $schema->createTable('growth_audit_event');
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('campaign_id', 'integer', ['notnull' => false]);
            $table->addColumn('event_name', 'string', ['length' => 80]);
            $table->addColumn('actor', 'string', ['length' => 180, 'notnull' => false]);
            $table->addColumn('context', 'json');
            $table->addColumn('created_at', 'datetime_immutable');
            $table->setPrimaryKey(['id']);
            $table->addIndex(['campaign_id'], 'idx_growth_audit_campaign');
            $table->addIndex(['event_name'], 'idx_growth_audit_event');
            $table->addForeignKeyConstraint('growth_campaign', ['campaign_id'], ['id'], ['onDelete' => 'SET NULL']);
        }
    }

    public function down(Schema $schema): void
    {
        foreach (['growth_audit_event', 'growth_publication', 'growth_content', 'growth_campaign'] as $table) {
            if ($schema->hasTable($table)) {
                $schema->dropTable($table);
            }
        }
    }
}
