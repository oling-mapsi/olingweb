# I18N-5E - Homepage + Global Core

Date: 2026-10-02

Base commit: `bf2f5308b6c4582133a86cefaf2c6e7da89c9e21`

## Result

LOT RESULT: PARTIAL

HOMEPAGE RESULT: PASS

PUBLICSITECONFIG HOMEPAGE CONTENT: PASS

CORE WEBSITE RESULT: PARTIAL

## Done

- Created versioned homepage source: `data/i18n/home_page.fr.json`.
- Created idempotent command: `app:i18n:backfill-home-page`.
- Added `--dry-run` and `--overwrite` support.
- Backfilled `SitePageTranslation(fr).structuredData.homePage`.
- Switched `PublicSitePageResolver::getHomePage()` to read Doctrine structured data.
- Removed editorial homepage payload from `PublicSiteConfig::getHome()`.
- Removed Twig inline `homeUniverseCards`; cards now come from Doctrine payload.
- Added test coverage for the homepage source file.

## Homepage Matrix

| Zone | Source after |
|---|---|
| hero | `SitePageTranslation.structuredData.homePage` |
| practice blocks | `SitePageTranslation.structuredData.homePage.practices.cards` + linked `PracticeTranslation` for images/entity matching |
| service blocks | `SitePageTranslation.structuredData.homePage` |
| cards | `SitePageTranslation.structuredData.homePage.practices.cards` |
| key figures | `SitePageTranslation.structuredData.homePage.kpis` |
| proof points | `SitePageTranslation.structuredData.homePage.proof` |
| MAPSI promo | `SitePageTranslation.structuredData.homePage.practices.cards` |
| CTA | `SitePageTranslation.structuredData.homePage.finalCta` |
| resources CTA | `SitePageTranslation.structuredData.homePage.resources.cta` |

Homepage public editorial hardcoded after: 0 known groups in `templates/index.html.twig` and `PublicSiteConfig::getHome()`.

## Backfill Validation

- Dry-run before write: `homePage created dry_run=yes`
- Write: `homePage created dry_run=no`
- Second run: `homePage unchanged dry_run=no`

## Remaining Core

- Non-homepage `PublicSiteConfig` editorial pages remain hardcoded.
- Team defaults remain hardcoded.
- Non-homepage Twig fallbacks remain partial.
- ERP questionnaire remains deferred.
- AI consultant remains deferred.

## KPIs

- Homepage editorial hardcoded after: 0 known groups
- PublicSiteConfig homepage editorial after: 0
- Core website public editorial hardcoded after: at least 20 groups
- Public microcopy hardcoded after: at least 3 groups

## Runtime

- FR URLs: unchanged
- Default locale: unchanged
- Public EN routes: no
- Public ES routes: no
- EN content: 0
- ES content: 0

## Next Recommended Lot

I18N-5F: migrate non-homepage `PublicSiteConfig` editorial pages and team defaults.
