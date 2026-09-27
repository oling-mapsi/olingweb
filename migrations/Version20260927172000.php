<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260927172000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update homepage project KPI count from 70+ to 150+';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE site_page SET body_html = JSON_SET(body_html, '$.kpis[1].label', '150+') WHERE slug = 'home' AND JSON_UNQUOTE(JSON_EXTRACT(body_html, '$.kpis[1].text')) = 'Projets SI et conformité référencés' AND JSON_UNQUOTE(JSON_EXTRACT(body_html, '$.kpis[1].label')) = '70+'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE site_page SET body_html = JSON_SET(body_html, '$.kpis[1].label', '70+') WHERE slug = 'home' AND JSON_UNQUOTE(JSON_EXTRACT(body_html, '$.kpis[1].text')) = 'Projets SI et conformité référencés' AND JSON_UNQUOTE(JSON_EXTRACT(body_html, '$.kpis[1].label')) = '150+'");
    }
}
