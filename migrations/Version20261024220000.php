<?php
namespace DoctrineMigrations;
use App\Service\Ai\AiTrackingSchema;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
final class Version20261024220000 extends AbstractMigration
{
    public function getDescription(): string {return 'Add native AI task bindings and idempotent tracking receipts';}
    public function up(Schema $schema): void {AiTrackingSchema::add($schema);}
    public function down(Schema $schema): void {$this->abortIf(true,'Preserve mission bindings and audit receipts; rollback application only.');}
}
