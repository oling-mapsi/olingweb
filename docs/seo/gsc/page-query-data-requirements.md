# GSC page x query requirements

Date de référence: 2026-08-17

## Objectif

Obtenir les exports `Search Console` qui permettent de trancher AMOA, ERP et les bascules `query -> old URL / new URL` sans 301 destructive.

## Exports requis

- Propriété: `https://oling.fr/`
- Type: `Search results`
- Dimensions: `date`, `query`, `page`
- Métriques: `clicks`, `impressions`, `ctr`, `position`
- Périodes:
  - `2026-07-13 -> 2026-08-09`
  - `2026-08-10 -> dernière journée complète disponible`
  - `2026-08-03 -> 2026-08-09`
  - `2026-08-10 -> 2026-08-16`

## Requêtes prioritaires

- `amoa erp`
- `amoa si`
- `cadrage stratégique amoa`
- `cabinet amoa`
- `cabinet de conseil amoa`
- `amoa crm`
- `conseil gmao`
- `amoa si finance`
- `expert rgpd`
- `audit de conformité ia`
- `conformité ia`
- `027001`
- `is027001`

## Pages prioritaires

- `https://oling.fr/amoa-si`
- `https://oling.fr/consulting/assistance-a-maitrise-douvrage`
- `https://oling.fr/practice/consulting`
- `https://oling.fr/business-apps/erp`
- `https://oling.fr/erp-progiciel`
- `https://oling.fr/crm`
- `https://oling.fr/gmao`
- `https://oling.fr/si-finance`
- `https://oling.fr/mapsi/integration-progiciel`
- `https://oling.fr/microsoft365/integration-progiciel`
- `https://oling.fr/expertises-audit/rgpd`
- `https://oling.fr/expertises-audit/conformite-ia-gouvernance-et-ai-act`
- `https://oling.fr/expertises/conformite-ia-gouvernance-ai-act`

## Livrables attendus

- Deltas `clicks`, `impressions`, `ctr`, `position` par `page x query`
- Top pertes URL
- Top pertes requêtes
- Top bascules `query -> URL`
- Mise en évidence des cas `www/non-www`

## Limite à rappeler

Les données `Search Console API` peuvent être tronquées ou échantillonnées selon les limites de l'API. Elles ne doivent pas être présentées comme un journal exhaustif de toutes les requêtes.
