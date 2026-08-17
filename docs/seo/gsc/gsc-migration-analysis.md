# Analyse GSC migration

Date de référence: 2026-08-17

## Statut

NON VERIFIE

## Constat

- Aucun export Google Search Console exploitable n'est présent dans le dépôt ou les fichiers inspectés au 2026-08-17.
- Aucun connecteur GSC utilisable n'est disponible dans l'environnement courant.
- Les données Search Console API peuvent être tronquées ou échantillonnées selon les limites de l'API; elles ne doivent pas être présentées comme un journal exhaustif de toutes les requêtes.

## Exports requis

- `docs/seo/gsc/gsc-page-query-before.csv` couvrant `2026-07-13` -> `2026-08-09`
- `docs/seo/gsc/gsc-page-query-after.csv` couvrant `2026-08-10` -> `2026-08-16`
- `docs/seo/gsc/gsc-page-query-week-before.csv` couvrant `2026-08-03` -> `2026-08-09`
- `docs/seo/gsc/gsc-page-query-week-after.csv` couvrant `2026-08-10` -> `2026-08-16`
- Dimensions minimales: `date`, `query`, `page`, `device`, `country`, `clicks`, `impressions`, `ctr`, `position`

## Analyses à produire dès réception

- `docs/seo/gsc/gsc-url-delta.csv`
- `docs/seo/gsc/gsc-query-delta.csv`
- `docs/seo/gsc/query-url-switches.csv`

## Questions à trancher dès réception

- Quelles anciennes URLs ont perdu leur trafic marque / hors marque.
- Quelles requêtes ont changé de page de destination après migration.
- Si `/amoa-si`, `/erp-progiciel`, `/crm`, `/gmao` récupèrent ou cannibalisent les URLs historiques.
