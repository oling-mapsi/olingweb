<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261002120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add SitePage translations foundation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE site_page_translation (id INT AUTO_INCREMENT NOT NULL, site_page_id INT NOT NULL, reviewed_by_id INT DEFAULT NULL, locale VARCHAR(5) NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(100) NOT NULL, hero_badge VARCHAR(255) DEFAULT NULL, hero_title VARCHAR(255) DEFAULT NULL, hero_intro LONGTEXT DEFAULT NULL, hero_side_html LONGTEXT DEFAULT NULL, body_html LONGTEXT DEFAULT NULL, seo_title VARCHAR(255) DEFAULT NULL, seo_description LONGTEXT DEFAULT NULL, og_title VARCHAR(255) DEFAULT NULL, og_description LONGTEXT DEFAULT NULL, image_alt LONGTEXT DEFAULT NULL, structured_data JSON DEFAULT NULL, translation_status VARCHAR(32) NOT NULL, source_content_hash VARCHAR(64) DEFAULT NULL, source_updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', translated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', reviewed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', published_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', unpublished_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX uniq_site_page_translation_locale (site_page_id, locale), UNIQUE INDEX uniq_site_page_translation_locale_slug (locale, slug), INDEX IDX_86B75395F6BD1646 (site_page_id), INDEX IDX_86B75395CFEA938A (reviewed_by_id), INDEX idx_site_page_translation_status (locale, translation_status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE localized_slug_history (id INT AUTO_INCREMENT NOT NULL, changed_by_id INT DEFAULT NULL, resource_type VARCHAR(64) NOT NULL, resource_id INT NOT NULL, locale VARCHAR(5) NOT NULL, old_slug VARCHAR(255) NOT NULL, new_slug VARCHAR(255) NOT NULL, changed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_5A0AA349EA0C8594 (changed_by_id), INDEX idx_localized_slug_history_lookup (resource_type, resource_id, locale, old_slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE site_page_translation ADD CONSTRAINT FK_86B75395F6BD1646 FOREIGN KEY (site_page_id) REFERENCES site_page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE site_page_translation ADD CONSTRAINT FK_86B75395CFEA938A FOREIGN KEY (reviewed_by_id) REFERENCES app_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE localized_slug_history ADD CONSTRAINT FK_5A0AA349EA0C8594 FOREIGN KEY (changed_by_id) REFERENCES app_user (id) ON DELETE SET NULL');

        $this->addSql(<<<'SQL'
INSERT INTO site_page_translation (
    site_page_id,
    locale,
    title,
    slug,
    hero_badge,
    hero_title,
    hero_intro,
    hero_side_html,
    body_html,
    seo_title,
    seo_description,
    translation_status,
    published_at,
    unpublished_at,
    created_at,
    updated_at
)
SELECT
    id,
    'fr',
    title,
    slug,
    hero_badge,
    hero_title,
    hero_intro,
    hero_side_html,
    body_html,
    title,
    meta_description,
    CASE
        WHEN publication_status IS NULL OR publication_status = 'published' THEN 'published'
        ELSE 'draft'
    END,
    CASE
        WHEN publication_status IS NULL OR publication_status = 'published' THEN COALESCE(published_at, CURRENT_TIMESTAMP)
        ELSE published_at
    END,
    unpublished_at,
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
FROM site_page
SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE localized_slug_history DROP FOREIGN KEY FK_5A0AA349EA0C8594');
        $this->addSql('ALTER TABLE site_page_translation DROP FOREIGN KEY FK_86B75395F6BD1646');
        $this->addSql('ALTER TABLE site_page_translation DROP FOREIGN KEY FK_86B75395CFEA938A');
        $this->addSql('DROP TABLE localized_slug_history');
        $this->addSql('DROP TABLE site_page_translation');
    }
}
