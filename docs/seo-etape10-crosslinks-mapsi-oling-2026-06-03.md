# Etape 10 SEO - Cross-links Oling <-> MAPSI

Date: 3 juin 2026

## 1) Objectif

Creer un maillage croise direct entre `oling.fr` et `mapsi.fr` pour:
- augmenter la pertinence thematique (AMOA SI, conformite, pilotage);
- renforcer l'autorite des 2 domaines;
- pousser les pages transactionnelles par des liens contextuels.

## 2) Verification prealable

Sitemaps controles le 3 juin 2026:
- `https://oling.fr/sitemap.default.xml`
- `https://mapsi.fr/sitemap.xml`

Constat:
- `oling.fr` contient deja des liens vers MAPSI (footer + pages mapsi), mais surtout vers `web.mapsi.fr`.
- `mapsi.fr` ne contient pas encore de liens explicites vers `oling.fr` sur les pages auditees.

## 3) Ce qui est implemente dans ce repo (Oling)

### 3.1 Migration DB-first

Fichier:
- `/Users/florestanrouet/myweb/olingw/migrations/Version20260603173000.php`

Actions:
- ajoute un bloc de cross-link vers `mapsi.fr` dans `site_page.hero_side_html` pour:
  - `amoa-si`
  - `si-finance`
  - `facturation-electronique-amoa`
  - `infrastructure-si-amoa`
  - `conformite-reglementaire`
  - `rgpd`
  - `cyber-securite`
  - `conseil-qualite`
- ajoute un bloc cross-link sur `ressources`.
- remplace le lien `href="/mapsi"` de la landing `mapsi-progiciel` par `https://mapsi.fr/`.
- tout est idempotent via marqueurs `SEO_STEP10_*`.

### 3.2 Schema entity linking

Fichier:
- `/Users/florestanrouet/myweb/olingw/config/services.yaml`

Ajout dans `oling_schema_same_as`:
- `https://mapsi.fr/`
- `https://web.mapsi.fr/`

### 3.3 Liens globaux template

Fichiers:
- `/Users/florestanrouet/myweb/olingw/templates/base.html.twig`
- `/Users/florestanrouet/myweb/olingw/templates/includes/sticky.html.twig`

Actions:
- footer: lien principal MAPSI sur `https://mapsi.fr/`.
- sticky: lien contextuel vers `https://mapsi.fr/fr/expertises/application-saas-grc`.

## 4) Backlinks reciproques a publier cote MAPSI

Backlog pret:
- `/Users/florestanrouet/myweb/olingw/output/spreadsheet/seo_crosslinks_mapsi_oling_step10.csv`

Lignes `direction=mapsi_to_oling`:
- mapping expertise MAPSI -> landing Oling (AMOA SI, SI finance, e-invoicing, infra, conformite, RGPD, cyber, qualite).
- mapping territoires MAPSI -> pages zones Oling.

## 5) Protocole d'application

### Local
1. `php bin/console doctrine:migrations:migrate --no-interaction`
2. Verifier:
   - `SELECT slug, LOCATE('SEO_STEP10_MAPSI_CROSSLINK', COALESCE(hero_side_html,'')) FROM site_page WHERE slug IN (...)`
3. Contrôle visuel des pages SEO.

### Production
1. deploy du code
2. `php bin/console doctrine:migrations:migrate --no-interaction`
3. verifier 5 URLs prioritaires:
   - `/amoa-si`
   - `/si-finance`
   - `/facturation-electronique-amoa`
   - `/infrastructure-si-amoa`
   - `/conformite-reglementaire`

## 6) Definition de fini (DoD)

Etape 10 terminee quand:
- tous les liens `oling_to_mapsi` sont visibles et crawlables;
- au moins 8 liens `mapsi_to_oling` sont publies cote MAPSI;
- les deux domaines apparaissent dans les `sameAs` et dans le maillage global;
- suivi GSC sur 2 cycles hebdo pour verifier impact impressions/clics.
