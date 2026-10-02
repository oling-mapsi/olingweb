# I18N-7 AI translation workflow - 2026-10-02

## Workflow

FR is the only source locale.

Target flow:

1. FR source content is hashed.
2. AI generates EN/ES localized content.
3. Result is validated before persistence.
4. Translation is stored with `translationStatus = ai_translated`.
5. Human review can move it to `to_review`, then `reviewed`.
6. Only a human publish action can move `reviewed -> published`.

AI generation never publishes content.

## Hash And Outdated

- `sourceContentHash` stores the current FR source hash at generation time.
- If current FR hash differs later, `isOutdated()` is true.
- `outdated` is not a status and does not unpublish public content.

## Slugs

- EN/ES slugs are generated independently from FR.
- Slugs must match `[a-z0-9][a-z0-9-]*`.
- `unique(locale, slug)` is checked before write.
- Collision returns `CONFLICT`; existing content is not overwritten silently.

## Validation

The workflow validates:

- required fields
- valid localized slug
- structured JSON shape and keys
- placeholder preservation
- source/target status rules

JSON keys are never translated. Only linguistic values are translated.

## CLI

Dry-run:

```bash
php bin/console app:i18n:translate --locale=en --dry-run --limit=10
```

Controlled generation:

```bash
php bin/console app:i18n:translate --locale=en --entity=SitePage --slug=contact --limit=1
```

Options:

- `--locale=en|es`
- `--entity=SitePage`
- `--id=...`
- `--slug=...`
- `--status=...`
- `--only-missing`
- `--only-outdated`
- `--overwrite`
- `--dry-run`
- `--limit=...`

Initial executable support is `SitePage`. The registry documents the workflow scope for `Practice`, `Service`, `Projet`, `Team`, `LegalPage`, `HomeSection`, `SiteGlobalContent`, ERP JSON and AI consultant JSON.

## Glossary

Versioned glossary: `data/i18n/glossary.json`.

It covers OLING, MAPSI, ERP, CRM, GMAO, AMOA, DSI, DPO, RGPD, NIS2, DORA, ISO 27001, ISO 9001, QSE, PCA/PRA and facturation électronique.

## Public Routing

Public localized routes still require:

```text
translationStatus = published
publishedAt != null
unpublishedAt = null
```

Therefore:

- `ai_translated` => 404
- `to_review` => 404
- `reviewed` => 404
- `published` => 200
