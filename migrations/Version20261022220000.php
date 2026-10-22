<?php
namespace DoctrineMigrations;

use App\Service\Crm\CrmSchema;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261022220000 extends AbstractMigration
{
    public function getDescription(): string { return 'CRM privé : affaires, sources dédupliquées, historique et synchronisation.'; }
    public function up(Schema $schema): void { CrmSchema::add($schema); }
    public function down(Schema $schema): void
    {
        $this->abortIf((int)$this->connection->fetchOne('SELECT COUNT(*) FROM crm_deal') > 0, 'CRM non vide : conserver les données lors du retour arrière du code.');
        foreach (['crm_source','crm_identity','crm_event','crm_state','crm_deal'] as $table) { $schema->dropTable($table); }
    }
}
