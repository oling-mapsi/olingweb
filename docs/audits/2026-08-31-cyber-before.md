# Audit avant - Cybersecurite OLING

Date d'audit local/prod: 2026-09-03.
Baseline validee: commit `1234111ed2ea6b1152bb9818a228c2fffaf02ae5` en local, origin et prod.

## URL Map

| URL | Statut | Title | H1 | Canonical | Indexable | Source | Profondeur |
| --- | --- | --- | --- | --- | --- | --- | --- |
| `/cyber-securite` | 200 | Conseil cyber securite \| Gouvernance SSI, risques et conformite \| OLING | Conseil cyber securite : proteger les SI critiques et la conformite | `https://oling.fr/cyber-securite` | oui | `site_page` + `templates/seo/_db-landing.html.twig` | 1319 mots |
| `/expertises-audit/si` | 200 | Securite des SI, ISO 27001, DORA et NIS2 \| OLING | Mieux tenir les risques cyber, SI et de continuite | `https://oling.fr/expertises-audit/si` | oui | `services` + template service | 1796 mots |
| `/expertise-cybersecurite-conformite-resilience` | 404 prod | OLING Page non trouvee | Oops ! La page que vous recherchez est introuvable. | `https://oling.fr/expertise-cybersecurite-conformite-resilience` | non | `site_page` non routee | 848 mots |

## Page `/cyber-securite`

- Meta avant: `Conseil cyber securite pour secteur public, PME et ETI : gouvernance SSI, reduction des risques, continuite d activite et conformite ISO 27001 NIS2 DORA.`
- H2 avant: Domaines d'intervention, Approche conseil, Guides associes, Cas clients et realisations, Outillage MAPSI associe, parcours, CTA, zones, FAQ.
- Schema avant: 3 scripts JSON-LD, dont `Service` et `FAQPage`.
- CTA avant: `Évaluer mon projet cyber`.
- Liens internes avant: `/expertises-audit/si`, `/rgpd`, `/mapsi-progiciel`, ressources cyber/NIS2/DORA/PCA-PRA.

## Faiblesses Avant

- Positionnement cyber correct mais trop court face aux concurrents.
- Intention "cabinet conseil cybersecurite / gouvernance SSI / ISO 27001 / NIS2 / DORA" insuffisamment explicite dans la structure.
- Peu de livrables visibles: SMSI, PSSI, dossier de preuves, PCA/PRA, RTO/RPO, plan de tests.
- Preuves projet presentes dans la base, mais plusieurs references cyber/DORA restent a anonymiser car non validees nominativement.
- RSSI externalise et EBIOS RM non documentes comme offres formelles: a ne pas promettre.
