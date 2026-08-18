# GSC Manual Export Procedure

Date de référence: 2026-08-18

## Objectif

Produire des exports GSC exploitables quand l'API n'est pas disponible.

`CONFIRMED`: un export `Pages.csv` + `Queries.csv` ne permet PAS de reconstruire un vrai mapping `query x page`.

Pour obtenir un vrai `query -> page`, il faut soit:

1. l'API Search Console ;
2. un export GSC filtré par page ;
3. un export GSC filtré par requête.

## Périodes prioritaires

- `2026-08-03` → `2026-08-09`
- `2026-08-10` → `2026-08-16`

Puis par page:

- `T+7`
- `T+14`
- `T+28`

## Exports minimums à produire

### Vue globale Pages

1. Ouvrir Google Search Console sur la propriété canonique `sc-domain:oling.fr` ou `https://oling.fr`.
2. Aller dans `Résultats de recherche`.
3. Régler `Type de recherche = Web`.
4. Régler la période voulue.
5. Ouvrir l'onglet `Pages`.
6. Exporter en CSV.

### Vue globale Requêtes

1. Même période.
2. Onglet `Requêtes`.
3. Exporter en CSV.

### Vrai mapping query x page

Pour chaque money page:

1. Rester dans `Résultats de recherche`.
2. Ajouter un filtre `Page = URL exacte`.
3. Conserver `Type de recherche = Web`.
4. Exporter l'onglet `Requêtes`.

Pages cibles:

- `https://oling.fr/amoa-si`
- `https://oling.fr/business-apps/erp`
- `https://oling.fr/crm`
- `https://oling.fr/gmao`
- `https://oling.fr/si-finance`

## Si la dimension date est disponible

Si GSC permet d'exporter la vue avec `Date`, exporter aussi:

1. période voulue ;
2. dimension `Dates` ;
3. filtre `Page = URL exacte` si possible ;
4. export CSV.

## Noms attendus

Voir [README.md](/Users/florestanrouet/myweb/olingw/docs/seo/phase2/measurement/import/README.md).

## Limites importantes

- `CONFIRMED`: les exports UI peuvent être tronqués par ligne.
- `CONFIRMED`: les exports UI ne sont pas un journal exhaustif de toutes les requêtes.
- `CONFIRMED`: un export global `Pages` et un export global `Requêtes` ne prouvent pas quelle page ranke sur quelle requête.
- `LIKELY`: les données les plus récentes peuvent être incomplètes à cause du délai de publication GSC.

## Recommandation

- priorité 1: API Search Console avec `date,query,page`
- priorité 2: exports filtrés par page pour les cinq money pages
- priorité 3: exports globaux `Pages` et `Requêtes` pour contexte macro uniquement
