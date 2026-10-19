<?php
declare(strict_types=1);
namespace DoctrineMigrations;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
final class Version20261016110000 extends AbstractMigration
{
    public function getDescription(): string { return 'Opaque contact receipt key and notification status; no historical conversion backfill'; }
    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE messagerie ADD submission_id VARCHAR(32) DEFAULT NULL, ADD notification_status VARCHAR(16) DEFAULT 'unknown' NOT NULL");
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CONTACT_SUBMISSION_ID ON messagerie (submission_id)');
    }
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Preserve contact receipts. Roll back application code only; do not delete receipt history.');
    }
}
