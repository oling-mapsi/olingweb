# Audit SEO pré-migration

Date de référence: 2026-08-17

## Résumé

- Fenêtre Git demandée: `2026-07-25 -> 2026-08-10`
- `CONFIRMED`: un seul commit versionné tombe dans cette fenêtre sur la branche inspectée: `0c78cf8f` du `2026-07-25`.
- `CONFIRMED`: ce commit ne modifie pas les routes publiques SEO, les titles, H1, canonical, sitemap, menus ou landings commerciales.
- `LIKELY`: les changements éditoriaux/BDD visibles publiquement autour du `2026-08-10` proviennent d'un travail non committé ou DB-first daté des `2026-08-08` et `2026-08-09`.
- `CONFIRMED`: la baisse observée sur certains signaux, dont `amoa erp`, ne peut donc pas être attribuée proprement à un commit Git SEO entre le `2026-07-25` et le `2026-08-10`.

## Tableau

| date | commit | file | URL affected | SEO element changed | old value | new value | query potentially affected | risk | confidence | recommended action |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 2026-07-25 | `0c78cf8f` | `public/.htaccess` | sitewide | front controller rewrite only | no `.htaccess` entry in commit history for this branch tip | front controller rewrite file added | none directly proven | low | `CONFIRMED` | preserve, then extend with canonical host redirect |
| 2026-07-25 | `0c78cf8f` | `src/Repository/SitePageRevisionRepository.php` | `/ressources/*` infra | revision repository only | previous repository state | Growth revision accessors hardened | resource articles only | low | `CONFIRMED` | no SEO root-cause evidence |
| 2026-08-08 | worktree evidence | `migrations/Version20260808120000.php` | `/ressources/*` | DB-first content injection | previous site_page state | multiple published resources dated `2026-08-08 12:00:00` | informational resources | medium | `LIKELY` | keep as migration evidence, not Git proof |
| 2026-08-08 | worktree evidence | `site_page` published rows | `/ressources/cadrage-projet-amoa-si` and related | new resource publication | absent or unpublished | 8 `growth-local-*` resources published | AMOA, RGPD, NIS2, quality | medium | `CONFIRMED` | use as timeline evidence for resource rollout |
| 2026-08-09 | worktree evidence | `migrations/Version20260809143000.php` | `/expertises/*` | public expertise pages created | absent | `/expertises/amoa-ia-pilotage-projets-agents`, `/expertises/conformite-ia-gouvernance-ai-act`, `/expertises/transformation-digitale-ia-pme-pmi` | AI Act, AMOA IA | medium | `LIKELY` | treat as post-`2026-08-09` expansion, not pre-`2026-08-03` cause |
| 2026-08-09 | worktree evidence | `migrations/Version20260809150000.php` | `/consulting/*`, `/expertises-audit/*`, `/business-apps/*` | new service rows | prior service set | new IA-oriented services inserted/updated | AI Act, AMOA IA | medium | `LIKELY` | treat as expansion, not ERP master evidence |

## Forensics `amoa erp`

- `CONFIRMED`: no committed Git SEO change between `2026-07-25` and `2026-08-10` explains the disappearance of impressions on query `amoa erp`.
- `LIKELY`: the root cause is outside committed Git in that window.
- Possible causes still open:
  - `SERP_VOLATILITY`
  - `CANNIBALIZATION`
  - `INSUFFICIENT_DATA`
  - `UNCOMMITTED_CONTENT_OR_DB_CHANGE`

## Operational reading

- Do not over-attribute pre-`2026-08-10` losses to the migration alone.
- Use Git as a negative proof here: the repository history does not show a canonical/title/route/sitemap event in the requested window that would independently explain the loss.
