# I18N-5G - Expertise, Sector, Practice Narratives, Team

Date: 2026-10-02

Base commit: `bf2f5308b6c4582133a86cefaf2c6e7da89c9e21`

## Result

LOT RESULT: PASS

PublicSiteConfig editorial business content: 0 for expertise pages, sector pages and practice narratives.

## Done

- Created `data/i18n/expertise_pages.fr.json` with 9 expertise pages.
- Created `data/i18n/sector_pages.fr.json` with 3 sector pages.
- Created `data/i18n/practice_narratives.fr.json` with 4 practice narratives.
- Added `app:i18n:backfill-expertise-sector-content`.
- Backfilled expertise and sector payloads into `SitePageTranslation.structuredData`.
- Backfilled practice narratives into `SiteGlobalContentTranslation`.
- Switched runtime reads to `PublicSitePageResolver`.
- Emptied `PublicSiteConfig::getExpertisePages()`, `getSectorPages()`, `getPracticeNarrative()`.
- Removed remaining `PublicSiteConfig` calls from `SeedPublicSitePagesCommand`.

## Team

- Dorothée Maitrias: DB row found, FR translation found, historical public profile restored into `team_translation.public_profile`.
- Claire Tillon: no DB row found, no current public usage, kept out of public rendering.

## Idempotence

- Expertise/sector/practice command second run: `write=0 unchanged=16 conflict=0 missing=0`.
- Team profile command second run: `write=0 unchanged=6 conflict=0 missing=0`.

## Deferred

- ERP questionnaire.
- AI consultant.
- Service narrative content still present in `PublicSiteConfig::getServiceNarrative()`, outside this lot.
