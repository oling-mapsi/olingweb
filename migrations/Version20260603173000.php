<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260603173000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Step 10: add DB-first cross-links between Oling expertise pages and mapsi.fr';
    }

    public function up(Schema $schema): void
    {
        $crosslinks = [
            'amoa-si' => 'https://mapsi.fr/fr/expertises/gestion-des-systemes-information',
            'si-finance' => 'https://mapsi.fr/fr/expertises/gestion-performance',
            'facturation-electronique-amoa' => 'https://mapsi.fr/fr/expertises/workflow-notifications',
            'infrastructure-si-amoa' => 'https://mapsi.fr/fr/expertises/itsm-et-gestion-des-services-it',
            'conformite-reglementaire' => 'https://mapsi.fr/fr/expertises/application-saas-grc',
            'rgpd' => 'https://mapsi.fr/fr/expertises/privacy-rgpd',
            'cyber-securite' => 'https://mapsi.fr/fr/expertises/securite-numerique-cybersecurite',
            'conseil-qualite' => 'https://mapsi.fr/fr/expertises/management-qualite',
        ];

        foreach ($crosslinks as $slug => $mapsiUrl) {
            $block = $this->buildExpertiseCrosslinkBlock($mapsiUrl);

            $this->addSql(
                "UPDATE site_page
                 SET hero_side_html = CONCAT(COALESCE(hero_side_html, ''), '\\n\\n', " . $this->q($block) . ")
                 WHERE slug = " . $this->q($slug) . "
                   AND (hero_side_html IS NULL OR hero_side_html NOT LIKE '%SEO_STEP10_MAPSI_CROSSLINK%')"
            );
        }

        $resourcesBlock = <<<HTML
<section class="oling-section oling-section--tight" data-seo-crosslink-mapsi="resources-v1">
  <div class="container">
    <div class="oling-surface p-4">
      <h2 class="h4 mb-2">Ressources complementaires MAPSI</h2>
      <p class="mb-3">Pour industrialiser la conformite, les workflows et le pilotage des preuves, consultez egalement les expertises MAPSI.</p>
      <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-primary btn-sm" href="https://mapsi.fr/fr/expertises/application-saas-grc" target="_blank" rel="noopener">MAPSI SaaS GRC</a>
        <a class="btn btn-outline-primary btn-sm" href="https://mapsi.fr/fr/expertises/privacy-rgpd" target="_blank" rel="noopener">MAPSI Privacy RGPD</a>
      </div>
    </div>
  </div>
</section>
<!-- SEO_STEP10_MAPSI_CROSSLINK_RESOURCES -->
HTML;

        $this->addSql(
            "UPDATE site_page
             SET hero_side_html = CONCAT(COALESCE(hero_side_html, ''), '\\n\\n', " . $this->q($resourcesBlock) . ")
             WHERE slug = 'ressources'
               AND (hero_side_html IS NULL OR hero_side_html NOT LIKE '%SEO_STEP10_MAPSI_CROSSLINK_RESOURCES%')"
        );

        $this->addSql(
            "UPDATE site_page
             SET hero_side_html = REPLACE(hero_side_html, 'href=\"/mapsi\"', 'href=\"https://mapsi.fr/\" target=\"_blank\" rel=\"noopener\"')
             WHERE slug = 'mapsi-progiciel'
               AND hero_side_html LIKE '%href=\"/mapsi\"%'"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            "UPDATE site_page
             SET hero_side_html = REPLACE(
                 hero_side_html,
                 'href=\"https://mapsi.fr/\" target=\"_blank\" rel=\"noopener\"',
                 'href=\"/mapsi\"'
             )
             WHERE slug = 'mapsi-progiciel'
               AND hero_side_html LIKE '%href=\"https://mapsi.fr/%'"
        );
    }

    private function buildExpertiseCrosslinkBlock(string $mapsiUrl): string
    {
        return <<<HTML
<section class="oling-section oling-section--tight" data-seo-crosslink-mapsi="expertise-v1">
  <div class="container">
    <div class="oling-surface p-4">
      <h2 class="h4 mb-2">Outillage MAPSI associe</h2>
      <p class="mb-3">Cette expertise peut etre industrialisee avec MAPSI pour renforcer la tracabilite, les workflows et les preuves d'audit.</p>
      <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-primary btn-sm" href="{$mapsiUrl}" target="_blank" rel="noopener">Voir le module MAPSI associe</a>
        <a class="btn btn-outline-primary btn-sm" href="https://mapsi.fr/fr/territoires/france-metropolitaine" target="_blank" rel="noopener">Couverture France et DROM</a>
      </div>
    </div>
  </div>
</section>
<!-- SEO_STEP10_MAPSI_CROSSLINK -->
HTML;
    }

    private function q(?string $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        return $this->connection->quote($value);
    }
}
