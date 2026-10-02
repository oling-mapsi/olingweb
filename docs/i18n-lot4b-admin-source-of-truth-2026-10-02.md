# I18N-4B — Admin Source Of Truth

Date: 2026-10-02

## Base

- Base commit: `40cde5ecf681a5a3e1548854199a7c1aff9909fc`
- Goal: BO writes `Translation(fr)` first, then syncs legacy fields.
- Public EN routes: none added.
- Public ES routes: none added.
- Default locale: unchanged.

## BO Matrix

| Entity | Current BO source before | Target/source after | Form | Controller | Gap |
|---|---|---|---|---|---|
| Practice | legacy root | `practice_translation(fr)` | `PracticeType` over `StructuredContentAdminFormData` | `PracticeAdminController` | EN/ES placeholders not rendered as tabs yet |
| Services | legacy root | `service_translation(fr)` | `ServicesType` over DTO | `ServicesAdminController` | EN/ES placeholders not rendered as tabs yet |
| Projet | legacy root | `projet_translation(fr)` | `ProjetType` over DTO | `ProjetAdminController` | EN/ES placeholders not rendered as tabs yet |
| Team | legacy root | `team_translation(fr)` | `TeamType` over DTO | `TeamAdminController` | EN/ES placeholders not rendered as tabs yet |
| LegalPage | legacy root | `legal_page_translation(fr)` | `LegalPageType` over DTO | `LegalPageAdminController` | EN/ES placeholders not rendered as tabs yet |
| HomeSection | legacy root | `home_section_translation(fr)` | `HomeSectionType` / `HomeAwardsSectionType` over DTO | `HomeSectionAdminController` | EN/ES placeholders not rendered as tabs yet |

## Flow After

1. Controller loads FR translation into `StructuredContentAdminFormData`.
2. Existing form edits this DTO.
3. `StructuredContentTranslationSynchronizer::save*FromForm()` writes FR translation.
4. Synchronizer copies translation fields back to legacy root fields.
5. Public front keeps reading FR wrappers from I18N-4.

## HomeCard Decision

`HomeCard` migration is not required.

Reason: the entity contains no visible editorial text. It only stores relations to `Services`, `Projet`, `Practice`, `Metier`, plus `createdAt`. Visible labels/descriptions come from related entities, now covered by their own FR translation tables.

## Synchronizer

`StructuredContentTranslationSynchronizer` now supports:

- form data loading from FR translation;
- save from admin DTO into FR translation;
- one-way sync `Translation(fr) -> legacy`;
- localized slug history for slug-bearing translation tables.

Technical initialization methods from I18N-4 remain for bootstrap/backfill only.

## Legacy Reads Classification

- Public linguistic legacy reads: 0 for migrated entities in migrated public render flows.
- Compatibility sync reads/writes: present in `StructuredContentTranslationSynchronizer`.
- Admin legacy reads: non-linguistic root fields remain read/written, such as images, relations, flags, URLs.
- Tests: explicit legacy-vs-translation tests remain.
- Non-linguistic legitimate reads: slugs for routing, images, relations, ranks, technical statuses.

## PublicSiteConfig / SEO Narratives / Team Defaults

- `PublicSiteConfig`: contains editorial French arrays; I18N-5 item.
- SEO narratives in controllers/services: editorial and partly hardcoded; I18N-5 item.
- Team defaults in `PracticeController::buildTeamProfiles()`: visible editorial fallback; I18N-5 item.
- Twig fallbacks such as `default('texte français')`: inventory and migration required in I18N-5.

## DB Counts

| Entity | Root | FR | EN | ES |
|---|---:|---:|---:|---:|
| SitePage | 70 | 70 | 0 | 0 |
| Practice | 4 | 4 | 0 | 0 |
| Service | 34 | 34 | 0 | 0 |
| Projet | 263 | 263 | 0 | 0 |
| Team | 7 | 7 | 0 | 0 |
| LegalPage | 4 | 4 | 0 | 0 |
| HomeSection | 4 | 4 | 0 | 0 |
| HomeCard | 8 | n/a | 0 | 0 |

## Public Baseline

Rendered HTTP 200:

- `/`
- `/amoa-si`
- `/erp-progiciel`
- `/rgpd`
- `/facturation-electronique-amoa`
- `/ressources`
- `/services`
- `/projets`
- `/a-propos/team`
- `/mentions-legales`
- `/a-propos/politiquergpd`
- `/practice/consulting`
- `/consulting/assistance-a-maitrise-douvrage`

## Tests

- `php -l` modified PHP files: pass.
- `php bin/console lint:container`: pass.
- `./vendor/bin/phpunit`: pass, 119 tests, 345 assertions.

## I18N-5 Qualified Items

- `PublicSiteConfig` editorial blocks.
- SEO landing narratives.
- team profile defaults.
- Twig French editorial fallbacks.
- homepage hardcoded cards/CTA.
- hardcoded FAQ and landing sections.
