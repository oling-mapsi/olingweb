# I18N-9 Wave 4A - Resource translations generation review

Date: 2026-10-03

## Scope

Authorized OpenAI scope: 5 public OLING.fr resources only.

- `ressource-choisir-cabinet-conseil-amoa-pme-eti`
- `ressource-transformation-si-secteur-public`
- `ressource-nis2-dora-par-ou-commencer`
- `ressource-feuille-route-cyber-pme-eti`
- `ressource-aipd-rgpd-methode`

## Outputs

- `data/i18n/waves/resources.wave4.en.json`
- `data/i18n/waves/resources.wave4.es.json`

Both snapshots contain 5 `SitePageTranslation` rows with status `ai_translated`.

## Reviewed slugs

### EN

- `choosing-it-project-advisory-consultancy-smes-mid-sized-companies`
- `public-sector-it-transformation-action-plan`
- `nis2-dora-where-to-start-2026`
- `cybersecurity-roadmap-smes-mid-sized-companies`
- `gdpr-dpia-when-how-to-conduct`

### ES

- `elegir-consultora-asesoria-proyectos-ti-pymes-empresas-medianas`
- `transformacion-sistemas-informacion-sector-publico`
- `nis2-dora-por-donde-empezar`
- `hoja-ruta-ciberseguridad-pymes-empresas-medianas`
- `eipd-rgpd-cuando-realizarla-metodo`

## Controls

- Dry-run generation: OK, 10 missing translations planned.
- Generation: OK, 5 EN + 5 ES.
- One ES slug was rejected by validation on first attempt, then regenerated successfully.
- Snapshot import dry-run:
  - EN: `rows=5 created=0 updated=0 unchanged=5 conflict=0`
  - ES: `rows=5 created=0 updated=0 unchanged=5 conflict=0`
- No published status in snapshots.
- No `/en/` or `/es/` links detected in snapshots.
- No automatic publication performed.
