<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260711110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Growth publishing metadata and revisions for resource articles';
    }

    public function up(Schema $schema): void
    {
        $sitePage = $schema->getTable('site_page');

        if (!$sitePage->hasColumn('external_id')) {
            $sitePage->addColumn('external_id', 'string', ['length' => 190, 'notnull' => false]);
            $sitePage->addUniqueIndex(['external_id'], 'uniq_site_page_external_id');
        }
        if (!$sitePage->hasColumn('publication_status')) {
            $sitePage->addColumn('publication_status', 'string', ['length' => 32, 'notnull' => false]);
        }
        if (!$sitePage->hasColumn('canonical_url')) {
            $sitePage->addColumn('canonical_url', 'string', ['length' => 255, 'notnull' => false]);
        }
        if (!$sitePage->hasColumn('categories')) {
            $sitePage->addColumn('categories', 'json', ['notnull' => false]);
        }
        if (!$sitePage->hasColumn('tags')) {
            $sitePage->addColumn('tags', 'json', ['notnull' => false]);
        }
        if (!$sitePage->hasColumn('publication_date')) {
            $sitePage->addColumn('publication_date', 'datetime_immutable', ['notnull' => false]);
        }
        if (!$sitePage->hasColumn('author_display_name')) {
            $sitePage->addColumn('author_display_name', 'string', ['length' => 255, 'notnull' => false]);
        }
        if (!$sitePage->hasColumn('source_campaign_id')) {
            $sitePage->addColumn('source_campaign_id', 'string', ['length' => 190, 'notnull' => false]);
        }
        if (!$sitePage->hasColumn('published_at')) {
            $sitePage->addColumn('published_at', 'datetime_immutable', ['notnull' => false]);
        }
        if (!$sitePage->hasColumn('unpublished_at')) {
            $sitePage->addColumn('unpublished_at', 'datetime_immutable', ['notnull' => false]);
        }

        if (!$schema->hasTable('site_page_revision')) {
            $revision = $schema->createTable('site_page_revision');
            $revision->addColumn('id', 'integer', ['autoincrement' => true]);
            $revision->addColumn('site_page_id', 'integer');
            $revision->addColumn('revision_number', 'integer');
            $revision->addColumn('revision_state', 'string', ['length' => 32]);
            $revision->addColumn('title', 'string', ['length' => 255]);
            $revision->addColumn('slug', 'string', ['length' => 255]);
            $revision->addColumn('excerpt', 'text');
            $revision->addColumn('content_html', 'text');
            $revision->addColumn('meta_title', 'string', ['length' => 255]);
            $revision->addColumn('meta_description', 'text');
            $revision->addColumn('canonical_url', 'string', ['length' => 255, 'notnull' => false]);
            $revision->addColumn('featured_image', 'string', ['length' => 255, 'notnull' => false]);
            $revision->addColumn('categories', 'json');
            $revision->addColumn('tags', 'json');
            $revision->addColumn('publication_date', 'datetime_immutable');
            $revision->addColumn('status', 'string', ['length' => 32]);
            $revision->addColumn('author_display_name', 'string', ['length' => 255]);
            $revision->addColumn('source_campaign_id', 'string', ['length' => 190]);
            $revision->addColumn('created_at', 'datetime_immutable');
            $revision->setPrimaryKey(['id']);
            $revision->addUniqueIndex(['site_page_id', 'revision_number'], 'uniq_site_page_revision_number');
            $revision->addIndex(['site_page_id', 'revision_state'], 'idx_site_page_revision_lookup');
            $revision->addForeignKeyConstraint('site_page', ['site_page_id'], ['id'], ['onDelete' => 'CASCADE']);
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('site_page_revision')) {
            $schema->dropTable('site_page_revision');
        }

        $sitePage = $schema->getTable('site_page');
        foreach ([
            'unpublished_at',
            'published_at',
            'source_campaign_id',
            'author_display_name',
            'publication_date',
            'tags',
            'categories',
            'canonical_url',
            'publication_status',
            'external_id',
        ] as $column) {
            if ($sitePage->hasColumn($column)) {
                $sitePage->dropColumn($column);
            }
        }
    }
}
