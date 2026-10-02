# I18N-5H - Core Service Narratives + Microcopy

Date: 2026-10-02

Base commit: `bf2f5308b6c4582133a86cefaf2c6e7da89c9e21`

## Result

LOT RESULT: PARTIAL

Service narratives: PASS

PublicSiteConfig public editorial: PASS

Core Twig editorial fallbacks: PARTIAL

Core public microcopy hardcoded: PARTIAL

## Done

- Created `data/i18n/service_narratives.fr.json` with 34 service narratives.
- Added `service_translation.public_narrative`.
- Added `app:i18n:backfill-service-narratives`.
- Backfilled 34 FR service narratives into `service_translation.public_narrative`.
- Switched service page runtime to `LocalizedContentResolver::getFrenchServiceView()->getPublicNarrative()`.
- Removed runtime use of `PublicSiteConfig::getServiceNarrative()`.
- Removed dead empty methods from `PublicSiteConfig`; it now only keeps the empty technical home skeleton used by admin/home bootstrap.
- Migrated service/practice/expertise generic fallbacks touched by this lot to Symfony Translation.
- Added `ServiceNarrativeSourceTest`.

## Idempotence

- `app:i18n:backfill-service-narratives` second run: `write=0 unchanged=34 conflict=0 missing=0`.

## Validation

- 34 service URLs smoke-tested: HTTP 200.
- Default locale observed: `en`.
- `/fr`, `/en`, `/es`: not introduced.
- EN content: 0.
- ES content: 0.

## Remaining

- ERP questionnaire: DEFERRED_ERP.
- AI consultant/chat: DEFERRED_AI.
- Legal fallback templates and some historical public/core blocks remain in the inventory and require a separate legal/base/navigation/admin-content cleanup before claiming full core microcopy = 0.

## KPI

- Service narrative hardcoded after: 0 runtime use.
- PublicSiteConfig public editorial after: 0.
- Core editorial hardcoded after: not 0.
- Core microcopy hardcoded after: not 0.
