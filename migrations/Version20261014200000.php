<?php
declare(strict_types=1);
namespace DoctrineMigrations;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
final class Version20261014200000 extends AbstractMigration
{
    public function getDescription(): string { return 'Add independent editorial hero image size.'; }
    public function up(Schema $schema): void { $this->addSql('ALTER TABLE site_page ADD hero_image_size VARCHAR(16) DEFAULT NULL'); }
    public function down(Schema $schema): void { $this->addSql('ALTER TABLE site_page DROP hero_image_size'); }
}
