<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261002210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add prompt version metadata to chat conversations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE chat_conversation ADD prompt_version VARCHAR(16) DEFAULT 'v1' NOT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chat_conversation DROP prompt_version');
    }
}
