# Phase 2A Dashboard

Date de référence: 2026-08-18
Période baseline GSC disponible: `2026-05-16` → `2026-08-15`

## Global

- `CONFIRMED`: les cinq money pages Phase 2A sont live.
- `CONFIRMED`: l'export GSC disponible reste un export agrégé `3 derniers mois`, pas un jeu dédié avant/après par page.
- `CONFIRMED`: `GSC_ACCESS_TOKEN` manque, donc aucun export `date x query x page` API n'a pu être récupéré via `scripts/gsc_search_analytics_fetch.php`.
- `NOT ENOUGH DATA`: les données GSC actuelles ne doivent pas être présentées comme un journal exhaustif de toutes les requêtes.

## Quick Wins

### Positions 11-20

| query | current_position | current_url | business_value | next_measurement_date | status |
| --- | ---: | --- | --- | --- | --- |
| `cadrage stratégique amoa` | 13.54 | `https://oling.fr/amoa-si` | `HIGH` | `2026-08-25` | `CONFIRMED` quick win prioritaire |
| `conseil gmao` | 19.05 | `https://oling.fr/gmao` | `HIGH` | `2026-08-25` | `CONFIRMED` quick win prioritaire |
| `pilotage amoa` | 16.00 | `https://oling.fr/amoa-si` | `MEDIUM` | `2026-08-25` | `POSSIBLE` faible volume |

### Positions 21-40

| query | current_position | current_url | business_value | next_measurement_date | status |
| --- | ---: | --- | --- | --- | --- |
| `amoa erp` | 21.46 | `https://oling.fr/business-apps/erp` | `HIGH` | `2026-08-25` | `CONFIRMED` volume le plus fort du lot |
| `amoa si` | 25.55 | `https://oling.fr/amoa-si` | `HIGH` | `2026-08-25` | `CONFIRMED` cluster central |
| `cabinet de conseil amoa` | 30.52 | `https://oling.fr/amoa-si` | `HIGH` | `2026-08-25` | `CONFIRMED` requête business utile |
| `cabinet amoa` | 26.24 | `https://oling.fr/amoa-si` | `HIGH` | `2026-08-25` | `CONFIRMED` requête business utile |
| `amoa epm` | 30.25 | `https://oling.fr/si-finance` | `MEDIUM` | `2026-08-25` | `LIKELY` adjacent SI Finance |
| `amoa si finance` | 29.67 | `https://oling.fr/si-finance` | `MEDIUM` | `2026-08-25` | `CONFIRMED` signal faible mais réel |

## AMOA SI

- T0: `0 clic`, `271 impressions`, `0% CTR`, `39.96`
- T+7: `WAITING_DATA`
- T+14: `WAITING_DATA`
- T+28: `WAITING_DATA`
- main queries: `amoa si`, `cadrage stratégique amoa`, `cabinet amoa`
- trend: `CONFIRMED` cluster le plus installé du lot
- status: `WATCH`

## ERP

- T0: `0 clic`, `66 impressions`, `0% CTR`, `35.27`
- T+7: `WAITING_DATA`
- T+14: `WAITING_DATA`
- T+28: `WAITING_DATA`
- main queries: `amoa erp`
- trend: `CONFIRMED` forte valeur business, encore hors Top 20
- status: `WATCH`

## CRM

- T0: `0 clic`, `9 impressions`, `0% CTR`, `18.56`
- T+7: `WAITING_DATA`
- T+14: `WAITING_DATA`
- T+28: `WAITING_DATA`
- main queries: `amoa crm`
- trend: `NOT ENOUGH DATA`
- status: `WATCH_LOW_VOLUME`

## GMAO

- T0: `1 clic`, `9 impressions`, `11.11% CTR`, `19.22`
- T+7: `WAITING_DATA`
- T+14: `WAITING_DATA`
- T+28: `WAITING_DATA`
- main queries: `conseil gmao`
- trend: `CONFIRMED` meilleur signal qualitatif du lot à date
- status: `WATCH`

## SI Finance

- T0: `0 clic`, `2 impressions`, `0% CTR`, `6.00`
- T+7: `WAITING_DATA`
- T+14: `WAITING_DATA`
- T+28: `WAITING_DATA`
- main queries: `amoa si finance`, `amoa epm`, `refonte erp finance`
- trend: `NOT ENOUGH DATA` sur le volume, mais `CONFIRMED` sur un signal business existant
- status: `WATCH_LOW_VOLUME`

## WWW Residual

- `CONFIRMED`: `https://www.oling.fr/gmao` = `26 impressions`
- `CONFIRMED`: `https://www.oling.fr/crm` = `4 impressions`
- `LIKELY`: le `www` va décroître progressivement après la normalisation host, sans disparition instantanée

## API Gap

- élément manquant exact: `GSC_ACCESS_TOKEN`
- effet: impossible d'extraire `date,query,page,clicks,impressions,ctr,position` sur `PRE 2026-08-03 → 2026-08-09` et `POST 2026-08-10 → 2026-08-16`
- prochaine action: injecter `GSC_ACCESS_TOKEN` en local ou fournir exports GSC dédiés `page x query`
