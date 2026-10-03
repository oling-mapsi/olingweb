# I18N-9 Wave 3 — Sectors / Projects / Team

Date: 2026-10-03

Base prod head: `2403b3c9 Fix localized French paths for I18N pages`

## Inventory

Real public scope audited from prod:

- Sector SitePages: `secteurs-index`, `secteur-industrie`, `secteur-services`, `secteur-secteur-public`.
- Project translations: 263 FR `published` project rows; initial batch limited to 10 per locale.
- Team translations: 7 FR `published` profiles: Florestan Rouet, Dorothée Maitrias, Manuel Feuillard, Julien Pujol, Gilbert Rinaldo, Hanna BADAN, Jean Claude VATI.

Team FR route:

- `/a-propos/team` returns 200 and is the canonical FR route.
- `/team` returns 404.
- Fixed localized FR path generation so hreflang/x-default for `team` points to `/a-propos/team`.

## Generation

OpenAI generation was limited to already public OLING.fr content.

Generated:

- Sectors EN: 3 `to_review`
- Sectors ES: 3 `to_review`
- Projects EN: 10 `to_review`
- Projects ES: 10 `to_review`
- Team EN: 7 `to_review`
- Team ES: 7 `to_review`

No Wave 3 page was published automatically.

## Review Notes

Terminology:

- `projets` kept as projects/engagements, not generic success stories.
- Client names, person names, LinkedIn URLs, photos and official organisation names preserved.
- Project period labels localized when they contain language, while dates remain unchanged.
- `Grand Port Maritime` kept as an official name.

Manual technical corrections:

- Retried `secteur-industrie` EN after an invalid AI slug.
- Completed Hanna BADAN EN with a shape-preserving translation after repeated JSON-shape failures.
- Localized project period labels containing French text.

## Snapshots

- `data/i18n/waves/sectors.wave3.en.json` — 4 rows: 1 reviewed index, 3 to_review sector pages.
- `data/i18n/waves/sectors.wave3.es.json` — 4 rows: 1 reviewed index, 3 to_review sector pages.
- `data/i18n/waves/projects.wave3.en.json` — 10 rows, to_review.
- `data/i18n/waves/projects.wave3.es.json` — 10 rows, to_review.
- `data/i18n/waves/team.wave3.en.json` — 7 rows, to_review.
- `data/i18n/waves/team.wave3.es.json` — 7 rows, to_review.

## Publication

Publication status: not published in this batch.

Reason: generated project/team/sector content is staged for human review. Team EN/ES publication also requires rendering the real team page/cards from `TeamTranslation`, not the generic SitePage template.

## Backup

Prod backup before generation:

- `/var/backups/oling/mysql/i18n-9-wave3-20261003-095800/all-databases.sql`
- SHA256 `a23cdb758598f9b1bea28969d3c9815b3eb800781976af56edd97ca4e574cea3`

## Result

Lot result: COMPLETE for controlled Wave 3 generation and reproducible snapshots.

Remaining work: human review, localized rendering for team/project cards, then progressive publication.
