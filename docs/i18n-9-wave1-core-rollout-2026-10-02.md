# I18N-9 - Wave 1 core rollout EN/ES

Date : 2026-10-02

Base commit : `62b000c6`

## Inventaire initial

| Type | FR | EN missing | EN translated | EN published | ES missing | ES translated | ES published |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| SitePageTranslation | 71 | 61 | 8 reviewed | 2 | 61 | 8 reviewed | 2 |
| PracticeTranslation | 4 | 4 | 0 | 0 | 4 | 0 | 0 |
| ServiceTranslation | 34 | 34 | 0 | 0 | 34 | 0 | 0 |
| ProjetTranslation | 263 | 263 | 0 | 0 | 263 | 0 | 0 |
| TeamTranslation | 7 | 7 | 0 | 0 | 7 | 0 | 0 |
| SiteGlobalContentTranslation | 4 | 4 | 0 | 0 | 4 | 0 | 0 |
| PageBlockTranslation | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| PageCtaTranslation | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| FaqItemTranslation | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| RelatedLinkTranslation | 0 | 0 | 0 | 0 | 0 | 0 | 0 |

Les contenus fichier FR presents : `home_page`, `core_pages`, `expertise_pages`, `sector_pages`, `landing_narratives`, `practice_narratives`, `service_narratives`, `team_profiles`, `erp_questionnaire`, `ai_consultant`.

## Wave 1 traitee

Slugs source :

- `apropos`
- `metiers`
- `team`
- `projets`
- `ressources`
- `expertises-index`
- `secteurs-index`

Les pages pilotes `services` et `contact` etaient deja `reviewed` en EN/ES et n'ont pas ete regenerees.

## Resultat Wave 1

| FR slug | EN slug | EN status | ES slug | ES status |
| --- | --- | --- | --- | --- |
| apropos | about | to_review | quienes-somos | to_review |
| metiers | careers | to_review | sectores-de-actividad | to_review |
| team | team | to_review | equipo | to_review |
| projets | projects | to_review | proyectos | to_review |
| ressources | resources | to_review | recursos | to_review |
| expertises-index | consulting-expertise | to_review | areas-de-especializacion | to_review |
| secteurs-index | industries | to_review | sectores | to_review |
| services | services | reviewed | servicios-y-ofertas | reviewed |
| contact | contact | reviewed | contacto | reviewed |

## Controles

- Appels OpenAI Wave 1 : 14 traductions creees, aucune publication.
- Snapshots reproductibles Wave 1 : `data/i18n/waves/site_pages.wave1.en.json` et `data/i18n/waves/site_pages.wave1.es.json`.
- Slug collision : 0.
- `sourceContentHash` : present sur les 14 traductions Wave 1.
- Workflow : `ai_translated` puis bascule controlee en `to_review`.
- Publication : aucune nouvelle page publiee.
- Correction technique : `max_output_tokens` OpenAI augmente a 8000 pour les pages longues.
- Garde-fou slug : fallback et validation etendus aux slugs Wave 1.

## Points de revue humaine

- `metiers` ES a produit `sectores-de-actividad`; a valider ou remplacer par `empleos`/`carreras` selon l'intention editorial exacte de la page.
- `projets` EN/ES a produit `projects`/`proyectos`; a valider contre l'intention "case studies / references".
- Les entites hors `SitePageTranslation` ne disposent pas encore d'un batch IA complet ; elles restent a traiter par vagues dediees.
