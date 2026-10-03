# I18N-9 Wave 4A-R - Resource human review and publication

Date: 2026-10-03

## Scope

Reviewed and prepared for publication:

- EN: 5 / 5 resources
- ES: 5 / 5 resources

Resources:

- `ressource-choisir-cabinet-conseil-amoa-pme-eti`
- `ressource-transformation-si-secteur-public`
- `ressource-nis2-dora-par-ou-commencer`
- `ressource-feuille-route-cyber-pme-eti`
- `ressource-aipd-rgpd-methode`

## Search intent

| Source | EN intent | ES intent |
|---|---|---|
| `ressource-choisir-cabinet-conseil-amoa-pme-eti` | how to choose an IT project advisory / ERP advisory consultancy for SMEs and mid-sized companies | elegir una consultora de asesoría en proyectos TI/SI para pymes y empresas medianas |
| `ressource-transformation-si-secteur-public` | public-sector IT / information systems transformation action plan | transformación de sistemas de información en el sector público |
| `ressource-nis2-dora-par-ou-commencer` | NIS2 and DORA starting point / implementation plan | NIS2 y DORA: por dónde empezar |
| `ressource-feuille-route-cyber-pme-eti` | cybersecurity roadmap for SMEs and mid-sized companies | hoja de ruta de ciberseguridad para pymes y empresas medianas |
| `ressource-aipd-rgpd-methode` | GDPR DPIA methodology | EIPD / RGPD metodología |

## Human review controls

- Structure: FR/EN/ES H2, H3, lists and tables counts match for the 5 resources.
- Tables: none in this batch.
- FAQ: translated and structurally aligned.
- Claims: no new unsupported claim detected.
- Regulatory: NIS2, DORA, GDPR/RGPD and DPIA/EIPD scope preserved.
- AMOA: not translated literally in EN; rendered as IT project advisory / implementation advisory.
- PME/ETI: rendered as SMEs / mid-sized companies, and pymes / empresas medianas.
- AIPD: EN uses DPIA / Data Protection Impact Assessment / GDPR; ES uses EIPD / RGPD.
- Internal links: FR fallback kept when localized target is not published; `/conseil-qualite` localized because the target is published.
- Source hash: present.
- `isOutdated`: false.

## Changes made

- EN link change:
  - `/conseil-qualite` -> `/en/quality-consulting`
- ES link change:
  - `/conseil-qualite` -> `/es/consultoria-calidad`
- Snapshot statuses:
  - `ai_translated` -> `published`
- `reviewedAt` and `publishedAt` set in snapshots.

## Final snapshots

- `data/i18n/waves/resources.wave4.en.json`
- `data/i18n/waves/resources.wave4.es.json`

## Local validation

- Import dry-run after local apply:
  - EN: `rows=5 created=0 updated=0 unchanged=5 conflict=0`
  - ES: `rows=5 created=0 updated=0 unchanged=5 conflict=0`
- `php bin/phpunit`: `153 tests, 491 assertions`
- `php bin/console lint:container --env=dev`: OK
- `php bin/console lint:twig templates/`: OK
- `php bin/console lint:yaml config/ translations/ data/i18n/ --parse-tags`: OK
