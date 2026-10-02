<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261002170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add structured translatable editorial content tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE page_block (id INT AUTO_INCREMENT NOT NULL, site_page_id INT NOT NULL, block_type VARCHAR(32) NOT NULL, sort_order INT NOT NULL, enabled TINYINT(1) NOT NULL, layout VARCHAR(64) DEFAULT NULL, variant VARCHAR(64) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, icon VARCHAR(64) DEFAULT NULL, technical_config JSON DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_46487886F6BD1646 (site_page_id), INDEX idx_page_block_page_sort (site_page_id, sort_order), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE page_block_translation (id INT AUTO_INCREMENT NOT NULL, page_block_id INT NOT NULL, reviewed_by_id INT DEFAULT NULL, locale VARCHAR(5) NOT NULL, eyebrow VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, subtitle LONGTEXT DEFAULT NULL, body_html LONGTEXT DEFAULT NULL, cta_label VARCHAR(255) DEFAULT NULL, image_alt LONGTEXT DEFAULT NULL, translation_status VARCHAR(32) NOT NULL, source_content_hash VARCHAR(64) DEFAULT NULL, translated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', reviewed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX uniq_page_block_translation_locale (page_block_id, locale), INDEX IDX_7F9284D275641D (page_block_id), INDEX IDX_7F9284D264B64DCC (reviewed_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE faq_item (id INT AUTO_INCREMENT NOT NULL, site_page_id INT NOT NULL, sort_order INT NOT NULL, enabled TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_47839D09F6BD1646 (site_page_id), INDEX idx_faq_item_page_sort (site_page_id, sort_order), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE faq_item_translation (id INT AUTO_INCREMENT NOT NULL, faq_item_id INT NOT NULL, reviewed_by_id INT DEFAULT NULL, locale VARCHAR(5) NOT NULL, question LONGTEXT NOT NULL, answer_html LONGTEXT DEFAULT NULL, translation_status VARCHAR(32) NOT NULL, source_content_hash VARCHAR(64) DEFAULT NULL, reviewed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX uniq_faq_item_translation_locale (faq_item_id, locale), INDEX IDX_A50433DF802F84E8 (faq_item_id), INDEX IDX_A50433DF64B64DCC (reviewed_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE page_cta (id INT AUTO_INCREMENT NOT NULL, site_page_id INT NOT NULL, placement VARCHAR(64) NOT NULL, url VARCHAR(255) DEFAULT NULL, sort_order INT NOT NULL, enabled TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_413140DAF6BD1646 (site_page_id), INDEX idx_page_cta_page_sort (site_page_id, sort_order), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE page_cta_translation (id INT AUTO_INCREMENT NOT NULL, page_cta_id INT NOT NULL, locale VARCHAR(5) NOT NULL, label VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, body LONGTEXT DEFAULT NULL, translation_status VARCHAR(32) NOT NULL, source_content_hash VARCHAR(64) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX uniq_page_cta_translation_locale (page_cta_id, locale), INDEX IDX_CC59EC3899784B01 (page_cta_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE related_link (id INT AUTO_INCREMENT NOT NULL, source_page_id INT NOT NULL, target_page_id INT DEFAULT NULL, external_url VARCHAR(255) DEFAULT NULL, sort_order INT NOT NULL, enabled TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_FEA655CD33711CB3 (source_page_id), INDEX IDX_FEA655CD5E9E89CB (target_page_id), INDEX idx_related_link_page_sort (source_page_id, sort_order), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE related_link_translation (id INT AUTO_INCREMENT NOT NULL, related_link_id INT NOT NULL, locale VARCHAR(5) NOT NULL, label VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, translation_status VARCHAR(32) NOT NULL, source_content_hash VARCHAR(64) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX uniq_related_link_translation_locale (related_link_id, locale), INDEX IDX_3F750EDFB416C57B (related_link_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE site_global_content (id INT AUTO_INCREMENT NOT NULL, identifier VARCHAR(64) NOT NULL, enabled TINYINT(1) NOT NULL, technical_config JSON DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_F56F463E772E836A (identifier), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE site_global_content_translation (id INT AUTO_INCREMENT NOT NULL, site_global_content_id INT NOT NULL, locale VARCHAR(5) NOT NULL, title VARCHAR(255) DEFAULT NULL, body_html LONGTEXT DEFAULT NULL, cta_label VARCHAR(255) DEFAULT NULL, translation_status VARCHAR(32) NOT NULL, source_content_hash VARCHAR(64) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX uniq_site_global_content_translation_locale (site_global_content_id, locale), INDEX IDX_15324559815753DC (site_global_content_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE page_block ADD CONSTRAINT FK_46487886F6BD1646 FOREIGN KEY (site_page_id) REFERENCES site_page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE page_block_translation ADD CONSTRAINT FK_7F9284D275641D FOREIGN KEY (page_block_id) REFERENCES page_block (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE page_block_translation ADD CONSTRAINT FK_7F9284D264B64DCC FOREIGN KEY (reviewed_by_id) REFERENCES app_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE faq_item ADD CONSTRAINT FK_47839D09F6BD1646 FOREIGN KEY (site_page_id) REFERENCES site_page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE faq_item_translation ADD CONSTRAINT FK_A50433DF802F84E8 FOREIGN KEY (faq_item_id) REFERENCES faq_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE faq_item_translation ADD CONSTRAINT FK_A50433DF64B64DCC FOREIGN KEY (reviewed_by_id) REFERENCES app_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE page_cta ADD CONSTRAINT FK_413140DAF6BD1646 FOREIGN KEY (site_page_id) REFERENCES site_page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE page_cta_translation ADD CONSTRAINT FK_CC59EC3899784B01 FOREIGN KEY (page_cta_id) REFERENCES page_cta (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE related_link ADD CONSTRAINT FK_FEA655CD33711CB3 FOREIGN KEY (source_page_id) REFERENCES site_page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE related_link ADD CONSTRAINT FK_FEA655CD5E9E89CB FOREIGN KEY (target_page_id) REFERENCES site_page (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE related_link_translation ADD CONSTRAINT FK_3F750EDFB416C57B FOREIGN KEY (related_link_id) REFERENCES related_link (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE site_global_content_translation ADD CONSTRAINT FK_15324559815753DC FOREIGN KEY (site_global_content_id) REFERENCES site_global_content (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_global_content_translation DROP FOREIGN KEY FK_15324559815753DC');
        $this->addSql('ALTER TABLE related_link_translation DROP FOREIGN KEY FK_3F750EDFB416C57B');
        $this->addSql('ALTER TABLE related_link DROP FOREIGN KEY FK_FEA655CD33711CB3');
        $this->addSql('ALTER TABLE related_link DROP FOREIGN KEY FK_FEA655CD5E9E89CB');
        $this->addSql('ALTER TABLE page_cta_translation DROP FOREIGN KEY FK_CC59EC3899784B01');
        $this->addSql('ALTER TABLE page_cta DROP FOREIGN KEY FK_413140DAF6BD1646');
        $this->addSql('ALTER TABLE faq_item_translation DROP FOREIGN KEY FK_A50433DF802F84E8');
        $this->addSql('ALTER TABLE faq_item_translation DROP FOREIGN KEY FK_A50433DF64B64DCC');
        $this->addSql('ALTER TABLE faq_item DROP FOREIGN KEY FK_47839D09F6BD1646');
        $this->addSql('ALTER TABLE page_block_translation DROP FOREIGN KEY FK_7F9284D275641D');
        $this->addSql('ALTER TABLE page_block_translation DROP FOREIGN KEY FK_7F9284D264B64DCC');
        $this->addSql('ALTER TABLE page_block DROP FOREIGN KEY FK_46487886F6BD1646');
        $this->addSql('DROP TABLE site_global_content_translation');
        $this->addSql('DROP TABLE site_global_content');
        $this->addSql('DROP TABLE related_link_translation');
        $this->addSql('DROP TABLE related_link');
        $this->addSql('DROP TABLE page_cta_translation');
        $this->addSql('DROP TABLE page_cta');
        $this->addSql('DROP TABLE faq_item_translation');
        $this->addSql('DROP TABLE faq_item');
        $this->addSql('DROP TABLE page_block_translation');
        $this->addSql('DROP TABLE page_block');
    }
}
