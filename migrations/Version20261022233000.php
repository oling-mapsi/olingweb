<?php
namespace DoctrineMigrations;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
final class Version20261022233000 extends AbstractMigration
{
    public function getDescription(): string { return 'CRM : conserver les demandes intégrales et leurs sources sans troncature SQL.'; }
    public function isTransactional(): bool { return false; }
    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName()==='mysql') {
            $this->addSql('ALTER TABLE crm_deal MODIFY data LONGTEXT NOT NULL');
            $this->addSql('ALTER TABLE crm_event MODIFY data LONGTEXT NOT NULL');
        }
    }
    public function down(Schema $schema): void { $this->abortIf(true,'Conserver le contenu intégral lors du retour arrière applicatif.'); }
}
