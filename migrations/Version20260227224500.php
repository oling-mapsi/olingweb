<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260227224500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add featured_home_rank to practice and create home_section table';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('practice') && !$schema->getTable('practice')->hasColumn('featured_home_rank')) {
            $this->addSql('ALTER TABLE practice ADD featured_home_rank INT DEFAULT NULL');
        }
        if (!$schema->hasTable('home_section')) {
            $this->addSql('CREATE TABLE home_section (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(190) NOT NULL, title VARCHAR(255) DEFAULT NULL, intro LONGTEXT DEFAULT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_9F3C8C2B989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE home_section');
        $this->addSql('ALTER TABLE practice DROP featured_home_rank');
    }
}
