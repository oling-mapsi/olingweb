# I18N-4 — Entity + Translation

Date: 2026-10-02

## Base

- Base commit: `2788a7cd03fdfe8032d53fd5c7d29d57ab8e7426`
- Public EN routes added: no
- Public ES routes added: no
- Default locale changed: no
- Legacy columns removed: no

## Cartography

### Practice

- Public usage: homepage cards, practice pages, service links, navigation.
- Linguistic fields: `designation`, `slug`, `designationShort`, `h1Title`, `introduction`, `introductionShort`, `description`, `descriptionShort`, `tags`.
- Common fields: images, icon, CSS class, color, featured flags/rank, relations.
- Slug: yes.
- SEO: indirect through page/narrative.
- Admin: `PracticeAdminController`, `PracticeType`.
- Public controllers/templates: `PracticeController`, `PublicSiteController`, `Seo*Controller`, `index.html.twig`, `practice-home.html.twig`, `practices.html.twig`, base/menu includes.

### Services

- Public usage: service pages, practice cards, navigation.
- Linguistic fields: `designation`, `slug`, `designationShort`, `introductionShort`, `description`, `descriptionShort`.
- Common fields: images, icon, practice relation, teams, projects.
- Slug: yes.
- SEO: indirect through narrative.
- Admin: `ServicesAdminController`, `ServicesType`.
- Public controllers/templates: `PracticeController`, `services.html.twig`, `practice-home.html.twig`, menus.

### Projet

- Public usage: project listing, home project cards, service/practice related cards.
- Linguistic fields: `designation`, `slug`, `description`, `shortDescription`, `clientName`, `territory`, `periodLabel`.
- Common fields: images, relations, featured flags/rank, external id, public URL, status fields, software taxonomy, metadata.
- Slug: yes.
- SEO: no standalone public project page in this lot.
- Admin: `ProjetAdminController`, `ProjetType`.
- Public controllers/templates: `PracticeController`, `PublicSiteController`, `projets.html.twig`, mini cards/includes.

### Team

- Public usage: team page, practice/service people blocks.
- Linguistic fields: `titre`, `shortcv`.
- Common fields: name, photo, LinkedIn URL, relations.
- Slug: no entity slug.
- SEO: no.
- Admin: `TeamAdminController`, `TeamType`.
- Public controllers/templates: `PracticeController`, `team.html.twig`, practice/service templates.

### LegalPage

- Public usage: legal pages.
- Linguistic fields: `slug`, `title`, `body`.
- Common fields: updated date.
- Slug: yes.
- SEO: fixed template titles remain unchanged.
- Admin: `LegalPageAdminController`, `LegalPageType`.
- Public controllers/templates: `PracticeController`, legal templates.

### HomeSection

- Public usage: homepage sections.
- Linguistic fields: `slug`, `title`, `eyebrow`, `intro`, `ctaLabel`, `ctaLabelSecondary`.
- Common fields: CTA URLs, updated date.
- Slug: yes, admin identifier.
- SEO: no.
- Admin: `HomeSectionAdminController`, `HomeSectionType`, `HomeAwardsSectionType`.
- Public controllers/templates: homepage.

### HomeCard

- Public usage: manual home highlights.
- Linguistic fields: none on the entity.
- Common fields: relations to service/project/practice/metier, created date.
- Migration: not migrated, because the text comes from related entities.

## Tables Created

- `practice_translation`
- `service_translation`
- `projet_translation`
- `team_translation`
- `legal_page_translation`
- `home_section_translation`

Each table has `locale`, `translation_status`, `source_content_hash`, `source_updated_at`, `created_at`, `updated_at`, and `UNIQUE(root_id, locale)`. Slug-bearing tables also have `UNIQUE(locale, slug)`.

## Front

Public rendering uses `LocalizedContentResolver` and `TranslatedEntityPublicView`.

The Twig property names are intentionally preserved. Static grep still sees calls such as `practice.designation`, but in migrated public controllers those variables are translated views, not root entities.

## Back Office And Sync

Admin forms remain visually unchanged. After create/edit, `StructuredContentTranslationSynchronizer` writes FR legacy content into the matching translation table. Legacy columns are preserved and remain synchronized from the current BO write path.

## Database Counts

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

FR coverage for migrated entities: 100%.

## Public URLs Rendered

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

All returned HTTP 200.

## Legacy Reads

- Raw static target property refs before: 71.
- Raw static target property refs after: 71.
- Effective public source after: FR translation views for migrated controllers/templates.
- Remaining raw refs are accepted because Twig names were preserved and backed by translated views, or because admin/non-front code still edits legacy fields before synchronization.

## Hardcoded Editorial Content

- Broad static count before: 30032.
- Broad static count after: 30038.
- Increase comes from migration/docs/test literals.
- Large remaining hardcoded editorial surface remains in Twig/PHP narratives and belongs to I18N-5.

## CONTENT-IMPROVEMENT

- `PublicSiteConfig` still contains many French narrative arrays.
- `buildTeamProfiles()` contains curated public team defaults.
- SEO landing narrative arrays remain hardcoded.
- Several Twig templates keep fallback French copy.

## Tests

- `php -l` on modified PHP files: pass.
- `php bin/console lint:container`: pass.
- `./vendor/bin/phpunit`: pass, 118 tests, 341 assertions.
