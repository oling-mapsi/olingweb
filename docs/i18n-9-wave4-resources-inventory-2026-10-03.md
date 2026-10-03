# I18N-9 Wave 4 - Resources inventory

Date: 2026-10-03

## Model observed

- Public resources are `SitePage` rows:
  - index: `ressources`
  - articles: `ressource-*`
- Translatable source of truth: `site_page_translation`.
- Public FR routes: `/ressources`, `/ressources/{slug}`.
- Localized resource routes prepared for review/publication:
  - EN: `/en/resources`, `/en/resources/{localizedSlug}`
  - ES: `/es/recursos`, `/es/recursos/{localizedSlug}`

## Production inventory

| id | slug | published | author | FR chars | EN | ES |
|---:|---|---|---|---:|---|---|
| 90 | `ressource-qse-performance-et-controle-interne-installer-ladhesion-dans-un-cadre-portuaire-public` | 2026-09-27 | Growth Factory | 5311 | missing | missing |
| 89 | `ressource-portail-data-territorial-structurer-la-trajectoire-eviter-leparpillement-et-installer-un-p` | 2026-09-27 | Growth Factory | 4551 | missing | missing |
| 74 | `ressource-crm-sur-mesure-structurer-la-cybers-curit-piloter-sans-illusion-ni-exposition` | 2026-08-18 | Growth Factory | 4835 | missing | missing |
| 73 | `ressource-amoa-gmao-eau-et-assainissement-structurer-l-amont-pour-s-curiser-la-trajectoire` | 2026-08-11 | Growth Factory | 5427 | missing | missing |
| 68 | `ressource-amoa-telco-clarifier-pour-mieux-d-cider-structurer-pour-mieux-avancer` | 2026-07-26 | Growth Factory | 3910 | missing | missing |
| 67 | `ressource-pca-pra-structurer-la-continuit-viter-les-illusions` | 2026-07-26 | Growth Factory | 4619 | missing | missing |
| 65 | `ressource-mapsi-comment-transformer-des-usages-disperses-en-pilotage-mesurable` | 2026-07-12 | Growth Factory | 2525 | missing | missing |
| 64 | `ressource-mapsi-passer-d-une-logique-d-outil-a-une-logique-d-adoption-mesurable` | 2026-07-12 | Growth Factory | 2646 | missing | missing |
| 48 | `ressource-choisir-cabinet-conseil-amoa-pme-eti` | n/a | n/a | 11690 | missing | missing |
| 47 | `ressource-transformation-si-secteur-public` | n/a | n/a | 12045 | missing | missing |
| 46 | `ressource-cadrage-projet-amoa-si` | n/a | n/a | 5423 | missing | missing |
| 45 | `ressource-indicateurs-qualite-si` | n/a | n/a | 5553 | missing | missing |
| 44 | `ressource-nis2-dora-par-ou-commencer` | n/a | n/a | 10531 | missing | missing |
| 43 | `ressource-feuille-route-cyber-pme-eti` | n/a | n/a | 11008 | missing | missing |
| 42 | `ressource-aipd-rgpd-methode` | n/a | n/a | 11426 | missing | missing |
| 41 | `ressource-registre-traitements-rgpd` | n/a | n/a | 5009 | missing | missing |

## Wave 4A proposed batch

Strict scope: public FR content only, no unpublished or confidential corpus.

1. `ressource-choisir-cabinet-conseil-amoa-pme-eti`
2. `ressource-transformation-si-secteur-public`
3. `ressource-nis2-dora-par-ou-commencer`
4. `ressource-feuille-route-cyber-pme-eti`
5. `ressource-aipd-rgpd-methode`

Rationale: first pass is capped at five long resources and covers the requested priority surface: AMOA/SI, public sector transformation, NIS2/DORA, cyber roadmap, GDPR/AIPD.

## Gate

Do not send this Wave 4A corpus to OpenAI until explicit user confirmation for Wave 4 is received.
