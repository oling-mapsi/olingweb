# I18N-9 Wave 2 — Services / Expertises

Date: 2026-10-03

Base prod head: `73a11f206124a4182cc59678f73cc7c7d49751e4`

## Inventory

Eligible FR SitePages found for Wave 2: ERP, CRM, GMAO, RGPD, cyber, facturation electronique, AMOA SI, SI finance, infrastructure SI, DSI externalisee, qualite, conformite reglementaire, risques/audit/controle interne, and expertise pages.

Already existing before Wave 2: ERP published EN/ES; CRM, GMAO, RGPD, cyber, facturation, AMOA SI reviewed EN/ES.

Wave 2 generated only missing SitePage translations first. Wave 2B adds executable AI translation, export and import support for ServiceTranslation and PracticeTranslation.

## Generated SitePages

EN generated: 10

- `si-finance` -> `finance-it-project-advisory`
- `infrastructure-si-amoa` -> `it-infrastructure-project-advisory`
- `dsi-externalisee` -> `outsourced-it-management`
- `conseil-qualite` -> `quality-consulting`
- `conformite-reglementaire` -> `regulatory-compliance`
- `gestion-risques-audit-controle-interne` -> `risk-audit-internal-control`
- `expertise-amoa-erp-applications-metiers` -> `erp-business-applications-advisory`
- `expertise-cybersecurite-conformite-resilience` -> `cybersecurity-compliance-resilience`
- `expertise-rgpd-dpo-gouvernance` -> `outsourced-dpo-ongoing-gdpr-governance`
- `expertise-data-automatisation-intelligence-artificielle` -> `data-automation-artificial-intelligence`

ES generated: 10

- `si-finance` -> `asesoria-si-finanzas`
- `infrastructure-si-amoa` -> `asesoria-infraestructura-si`
- `dsi-externalisee` -> `direccion-ti-externalizada`
- `conseil-qualite` -> `consultoria-calidad`
- `conformite-reglementaire` -> `cumplimiento-normativo`
- `gestion-risques-audit-controle-interne` -> `riesgos-auditoria-control-interno`
- `expertise-amoa-erp-applications-metiers` -> `asesoria-erp-aplicaciones-empresariales`
- `expertise-cybersecurite-conformite-resilience` -> `ciberseguridad-cumplimiento-resiliencia`
- `expertise-rgpd-dpo-gouvernance` -> `dpo-externo-gobernanza-rgpd-continua`
- `expertise-data-automatisation-intelligence-artificielle` -> `datos-automatizacion-inteligencia-artificial`

## Review

Automated validation: JSON shape preserved, placeholders preserved, slug collisions 0.

Manual correction applied: EN/ES title fields for `gestion-risques-audit-controle-interne`.

FR leakage scan: no blocking unexpected French leak detected in text fields. Accepted terms: `QSE`, `SI` in Spanish context.

Decision: keep all Wave 2 generated pages in `to_review`. No publication in this batch.

## Reproducibility

Snapshots:

- `data/i18n/waves/site_pages.wave2.en.json`
- `data/i18n/waves/site_pages.wave2.es.json`

ServiceTranslation and PracticeTranslation remain a gap for a follow-up implementation batch.

Wave 2B snapshots:

- `data/i18n/waves/services.wave2.en.json` — 34 ServiceTranslation rows, `to_review`
- `data/i18n/waves/services.wave2.es.json` — 34 ServiceTranslation rows, `to_review`
- `data/i18n/waves/practices.wave2.en.json` — 4 PracticeTranslation rows, `to_review`
- `data/i18n/waves/practices.wave2.es.json` — 4 PracticeTranslation rows, `to_review`

Wave 2B import dry-run was idempotent locally:

- services EN: 34 unchanged, 0 conflict
- services ES: 34 unchanged, 0 conflict
- practices EN: 4 unchanged, 0 conflict
- practices ES: 4 unchanged, 0 conflict

## Result

Lot result: COMPLETE for Wave 2B local generation and reproducible snapshots.

Reason: SitePage Wave 2 and Service/Practice Wave 2B generated and reproducible; no automatic publication.
