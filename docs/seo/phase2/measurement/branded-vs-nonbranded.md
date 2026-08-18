# Branded vs Non-Branded

Date de référence: 2026-08-18
Source principale: `docs/seo/gsc/import/Requêtes.csv`
Période: `2026-05-16` → `2026-08-15`

## Synthèse

| segment | clicks | impressions | ctr | weighted_position | confidence |
| --- | ---: | ---: | ---: | ---: | --- |
| BRANDED | 30 | 455 | 6.59% | 5.27 | `CONFIRMED` |
| NON_BRANDED_BUSINESS | 0 | 230 | 0.00% | 23.35 | `CONFIRMED` sur les requêtes existantes seulement |

## Lecture

- `CONFIRMED`: la traction existante est encore dominée par le brand (`oling`, `mapsi`).
- `CONFIRMED`: le business non-brand existe déjà sur AMOA, ERP, GMAO et SI Finance, mais le volume reste faible et aucun clic n'est encore observé dans cet export agrégé.
- `NOT ENOUGH DATA`: sans `page x query` API, il ne faut pas présenter cette vue comme un journal exhaustif de toutes les requêtes.

## Objectif de phase

- priorité business: croissance `NON_BRANDED_BUSINESS`
- priorité secondaire: réduction progressive du `www` résiduel
- priorité de mesure: stabiliser la lecture `page x query` dès que `GSC_ACCESS_TOKEN` sera disponible

## Blocage API

- `CONFIRMED`: `GSC_ACCESS_TOKEN` absent dans l'environnement local au 2026-08-18
- conséquence: impossible d'exécuter `scripts/gsc_search_analytics_fetch.php` et d'extraire `date,query,page,clicks,impressions,ctr,position`
- élément manquant exact: variable d'environnement `GSC_ACCESS_TOKEN`
