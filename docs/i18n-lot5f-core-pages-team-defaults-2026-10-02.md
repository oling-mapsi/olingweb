# I18N-5F - Core Pages + Team Defaults

Date: 2026-10-02

Base commit: `bf2f5308b6c4582133a86cefaf2c6e7da89c9e21`

## Result

LOT RESULT: PARTIAL

PUBLICSITECONFIG CORE EDITORIAL: PASS

TEAM DEFAULTS: PASS

## Done

- Created `data/i18n/core_pages.fr.json`.
- Created `app:i18n:backfill-core-pages`.
- Backfilled 8 FR core pages into `SitePageTranslation.structuredData.corePage`.
- Switched `PublicSitePageResolver::getEditorialPage()` to read Doctrine structured data.
- Emptied `PublicSiteConfig::getEditorialPages()`.
- Added `team_translation.public_profile`.
- Created `data/i18n/team_profiles.fr.json`.
- Created `app:i18n:backfill-team-profiles`.
- Backfilled 5 existing team public profiles into `team_translation.public_profile`.
- Removed the hardcoded team public profile catalog from `PracticeController`.

## Core Pages Backfill

Rows: 8

Imported:

- `apropos`
- `contact`
- `client`
- `services`
- `metiers`
- `team`
- `projets`
- `rse`

Idempotence:

- create/update run: `write=1 unchanged=7`
- second run: `write=0 unchanged=8`

## Team Profiles Backfill

Rows: 5

Imported:

- Florestan Rouet
- Manuel Feuillard
- Hanna Badan
- Julien Pujol
- Jean-Claude Vati

Deferred:

- Dorothée Maitrias: no local `Team` row matched.
- Claire Tillon: no local `Team` row matched.

Idempotence:

- first run: `write=5 unchanged=0`
- second run: `write=0 unchanged=5`

## Remaining

- `PublicSiteConfig::getExpertisePages()`
- `PublicSiteConfig::getSectorPages()`
- `PublicSiteConfig::getPracticeNarrative()`
- Deferred team profiles without local Team rows
- ERP questionnaire deferred
- AI consultant deferred

## KPIs

- PublicSiteConfig core editorial after: 0 in `getEditorialPages()`.
- Team editorial defaults after: 0 in `PracticeController`.
- Core Twig editorial fallbacks after: still partial.
- Public microcopy after: still partial.

## Runtime

- FR URLs: unchanged
- Default locale: unchanged
- Public EN routes: no
- Public ES routes: no
- EN content: 0
- ES content: 0

## Next Recommended Lot

I18N-5G: migrate expertise pages, sector pages and practice narratives from `PublicSiteConfig`; decide whether to create Dorothée Maitrias and Claire Tillon records or keep them out of public rendering.
