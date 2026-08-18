# Measurement Import

Déposer ici les exports GSC manuels normalisés.

## Fichiers attendus

- `gsc-2026-08-03_2026-08-09-pages.csv`
- `gsc-2026-08-03_2026-08-09-queries.csv`
- `gsc-2026-08-10_2026-08-16-pages.csv`
- `gsc-2026-08-10_2026-08-16-queries.csv`

Exports filtrés par page:

- `gsc-amoa-si-t7-queries.csv`
- `gsc-erp-t7-queries.csv`
- `gsc-crm-t7-queries.csv`
- `gsc-gmao-t7-queries.csv`
- `gsc-si-finance-t7-queries.csv`

Puis la même logique pour `t14` et `t28`.

## Règle critique

`CONFIRMED`: un export `Pages` + un export `Queries` ne permet pas de reconstruire `query x page`.

Pour un vrai mapping `query -> page`, fournir:

1. soit l'API Search Console ;
2. soit un export GSC filtré par page ;
3. soit un export GSC filtré par requête.
