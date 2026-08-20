<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260808123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align chat indexes with Doctrine-generated schema names';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();
        if ($platform !== 'mysql') {
            return;
        }

        $this->addSql('DROP INDEX idx_chat_conversation_retention ON chat_conversation');
        $this->addSql('DROP INDEX idx_chat_conversation_status ON chat_conversation');
        $this->addSql('ALTER TABLE chat_conversation RENAME INDEX uniq_chat_conversation_token TO UNIQ_74654F68AE981E3B');
        $this->addSql('ALTER TABLE chat_lead RENAME INDEX uniq_chat_lead_conversation TO UNIQ_8E0DFC8A9AC0396');
        $this->addSql('DROP INDEX idx_chat_message_sequence ON chat_message');
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();
        if ($platform !== 'mysql') {
            return;
        }

        $this->addSql('ALTER TABLE chat_conversation RENAME INDEX UNIQ_74654F68AE981E3B TO uniq_chat_conversation_token');
        $this->addSql('CREATE INDEX idx_chat_conversation_status ON chat_conversation (status)');
        $this->addSql('CREATE INDEX idx_chat_conversation_retention ON chat_conversation (retention_purge_at)');
        $this->addSql('ALTER TABLE chat_lead RENAME INDEX UNIQ_8E0DFC8A9AC0396 TO uniq_chat_lead_conversation');
        $this->addSql('CREATE INDEX idx_chat_message_sequence ON chat_message (conversation_id, sequence_number)');
    }
}
