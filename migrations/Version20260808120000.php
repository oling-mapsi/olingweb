<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260808120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add MVP chat conversation, message and lead storage';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('chat_conversation')) {
            $table = $schema->createTable('chat_conversation');
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('public_token', 'string', ['length' => 64]);
            $table->addColumn('status', 'string', ['length' => 32]);
            $table->addColumn('source_path', 'string', ['length' => 255, 'notnull' => false]);
            $table->addColumn('source_url', 'string', ['length' => 255, 'notnull' => false]);
            $table->addColumn('referrer', 'string', ['length' => 255, 'notnull' => false]);
            $table->addColumn('locale', 'string', ['length' => 10, 'notnull' => false]);
            $table->addColumn('ip_hash', 'string', ['length' => 64, 'notnull' => false]);
            $table->addColumn('user_agent_hash', 'string', ['length' => 64, 'notnull' => false]);
            $table->addColumn('qualification', 'json', ['notnull' => false]);
            $table->addColumn('summary_short', 'text', ['notnull' => false]);
            $table->addColumn('summary_long', 'text', ['notnull' => false]);
            $table->addColumn('started_at', 'datetime_immutable');
            $table->addColumn('last_message_at', 'datetime_immutable');
            $table->addColumn('submitted_at', 'datetime_immutable', ['notnull' => false]);
            $table->addColumn('expires_at', 'datetime_immutable');
            $table->addColumn('retention_purge_at', 'datetime_immutable');
            $table->addColumn('consent_at', 'datetime_immutable', ['notnull' => false]);
            $table->addColumn('email_sent_at', 'datetime_immutable', ['notnull' => false]);
            $table->setPrimaryKey(['id']);
            $table->addUniqueIndex(['public_token'], 'uniq_chat_conversation_token');
            $table->addIndex(['status'], 'idx_chat_conversation_status');
            $table->addIndex(['retention_purge_at'], 'idx_chat_conversation_retention');
        }

        if (!$schema->hasTable('chat_message')) {
            $table = $schema->createTable('chat_message');
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('conversation_id', 'integer');
            $table->addColumn('role', 'string', ['length' => 16]);
            $table->addColumn('content', 'text');
            $table->addColumn('message_type', 'string', ['length' => 32]);
            $table->addColumn('sequence_number', 'integer');
            $table->addColumn('source_urls', 'json', ['notnull' => false]);
            $table->addColumn('created_at', 'datetime_immutable');
            $table->setPrimaryKey(['id']);
            $table->addIndex(['conversation_id', 'sequence_number'], 'idx_chat_message_sequence');
            $table->addForeignKeyConstraint('chat_conversation', ['conversation_id'], ['id'], ['onDelete' => 'CASCADE']);
        }

        if (!$schema->hasTable('chat_lead')) {
            $table = $schema->createTable('chat_lead');
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('conversation_id', 'integer');
            $table->addColumn('full_name', 'string', ['length' => 255]);
            $table->addColumn('email', 'string', ['length' => 255]);
            $table->addColumn('phone', 'string', ['length' => 50]);
            $table->addColumn('company', 'string', ['length' => 255]);
            $table->addColumn('need_description', 'text');
            $table->addColumn('rgpd_consent', 'boolean');
            $table->addColumn('rgpd_consent_at', 'datetime_immutable');
            $table->addColumn('created_at', 'datetime_immutable');
            $table->setPrimaryKey(['id']);
            $table->addUniqueIndex(['conversation_id'], 'uniq_chat_lead_conversation');
            $table->addForeignKeyConstraint('chat_conversation', ['conversation_id'], ['id'], ['onDelete' => 'CASCADE']);
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('chat_lead')) {
            $schema->dropTable('chat_lead');
        }
        if ($schema->hasTable('chat_message')) {
            $schema->dropTable('chat_message');
        }
        if ($schema->hasTable('chat_conversation')) {
            $schema->dropTable('chat_conversation');
        }
    }
}
