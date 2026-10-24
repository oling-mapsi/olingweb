<?php
namespace DoctrineMigrations;
use App\Service\Ai\AiSchema;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
final class Version20261024180000 extends AbstractMigration
{
    public function getDescription(): string {return 'AI control: isolated missions, immutable prompts, runs, private assets, reviews, events and relay presence.';}
    public function up(Schema $schema): void {AiSchema::add($schema);}
    public function down(Schema $schema): void { $this->abortIf(true,'Keep AI records when rolling code back; deliberate data migration required.'); }
}
