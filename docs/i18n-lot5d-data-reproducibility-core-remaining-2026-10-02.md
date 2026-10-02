# I18N-5D - Data Reproducibility + Core Remaining

Date: 2026-10-02

Base commit: `bf2f5308b6c4582133a86cefaf2c6e7da89c9e21`

## Result

LOT RESULT: PARTIAL

DATA REPRODUCIBILITY: PASS

CORE WEBSITE RESULT: PARTIAL

This lot closes the I18N-5C reproducibility gap for SEO landing narratives. It does not complete the remaining core website migration.

## Reproducible Data Source

Source data: `data/i18n/landing_narratives.fr.json`

Target: `SitePageTranslation.structuredData.landingNarrative`

Matching key: `site_page.slug` + `site_page_translation.locale = fr`

Rows: 11

Command:

```bash
php bin/console app:i18n:backfill-landing-narratives --dry-run
php bin/console app:i18n:backfill-landing-narratives
```

Options:

- `--dry-run`: validate and report without writing.
- `--overwrite`: replace an existing different narrative.

## Validation Matrix

All 11 source keys were validated locally:

| Source key | SitePage | FR Translation | Existing narrative | Final action |
|---|---|---|---|---|
| conformite-reglementaire | YES | YES | YES | UNCHANGED |
| conseil-qualite | YES | YES | YES | UNCHANGED |
| crm | YES | YES | YES | UNCHANGED |
| cyber-securite | YES | YES | YES | RESTORED THEN UNCHANGED |
| direction-conformite-externalisee | YES | YES | YES | UNCHANGED |
| direction-qualite-deleguee | YES | YES | YES | UNCHANGED |
| dsi-externalisee | YES | YES | YES | UNCHANGED |
| gmao | YES | YES | YES | UNCHANGED |
| infrastructure-si-amoa | YES | YES | YES | UNCHANGED |
| rgpd | YES | YES | YES | UNCHANGED |
| si-finance | YES | YES | YES | UNCHANGED |

Reproducibility test:

1. Removed the `cyber-securite` target narrative locally.
2. Ran the command.
3. Result: `created=1 unchanged=10`.
4. Ran the command again.
5. Result: `created=0 unchanged=11`.

No duplicate data was created.

## Homepage Matrix

| Zone | Source actuelle | Modèle cible | Hardcoded? |
|---|---|---|---|
| hero | `PublicSiteConfig::getHome()` | `SitePageTranslation` / `HomeSectionTranslation` | yes |
| practice blocks | `templates/index.html.twig` + DB practices | `HomeSectionTranslation` + linked entities | yes |
| service blocks | `PublicSiteConfig::getHome()` | `HomeSectionTranslation` / `PageBlockTranslation` | yes |
| cards | inline Twig `homeUniverseCards` | linked entities or `PageBlockTranslation` | yes |
| key figures | `PublicSiteConfig::getHome()` | `HomeSectionTranslation` / KPI entities | partial |
| proof points | `PublicSiteConfig::getHome()` | `PageBlockTranslation` | yes |
| sector blocks | `PublicSiteConfig` | `PageBlockTranslation` / `SitePageTranslation` | yes |
| MAPSI promo | inline Twig / `PublicSiteConfig` | `PageBlockTranslation` | yes |
| CTA | `PublicSiteConfig::getHome()` | `PageCtaTranslation` | yes |
| footer promo | `base.html.twig` / `PublicSiteConfig` | `SiteGlobalContentTranslation` | yes |

Homepage editorial hardcoded after: not zero.

## PublicSiteConfig Classification

Keep structured non-linguistic:

- phone
- email
- URLs
- route names
- images
- legal identifiers
- schema technical values

Migrate editorial:

- homepage hero/sections
- editorial pages
- contact pitch
- client/reference page copy
- services page copy
- sectors/metiers copy
- team page copy
- project proof library copy
- expertise and sector page narratives

Status: not migrated in this lot.

## Team Defaults

`PracticeController::buildTeamProfiles()` still contains public editorial defaults:

- bios
- relationship text
- expertise area text
- title fallback

Status: not migrated in this lot.

## KPIs

- Qualified groups before: 37
- Public editorial hardcoded before: at least 27 groups
- Public editorial hardcoded after: at least 26 groups
- Core website public editorial hardcoded after: at least 24 groups
- Public microcopy hardcoded after: at least 3 groups
- Reproducible SEO narrative rows: 11
- ERP questionnaire: DEFERRED
- AI consultant: DEFERRED

## Runtime

No FR URL was changed.

Default locale: unchanged (`fr`)

Public EN routes: no

Public ES routes: no

EN content: 0

ES content: 0

## Next Recommended Lot

I18N-5E: migrate homepage and `PublicSiteConfig::getHome()` content to `HomeSectionTranslation`, `PageBlockTranslation`, `PageCtaTranslation` and `SiteGlobalContentTranslation`, then remove inline `homeUniverseCards`.
