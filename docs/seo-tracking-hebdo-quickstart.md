# Tracking SEO hebdo - mode d'emploi

## Fichier de suivi
- XLSX: `/Users/florestanrouet/myweb/olingw/output/spreadsheet/seo_tracking_hebdo_oling.xlsx`
- CSV: `/Users/florestanrouet/myweb/olingw/output/spreadsheet/seo_tracking_hebdo_oling.csv`

## Frequence
- Chaque lundi matin (periode: 7 derniers jours).

## Donnees a remplir
- `Impressions (GSC)`
- `Clics (GSC)`
- `Position moyenne (GSC)`
- `Conversions formulaire contact (GA4)`

Les colonnes `CTR %` et `Taux conv/clic %` se calculent automatiquement dans le XLSX.

## Extraction Google Search Console
- Rapport: `Performances > Resultats de recherche`.
- Filtre `Page` sur chaque URL de la feuille.
- Periode: `7 derniers jours`.
- Recopier impressions, clics, position.

## Extraction GA4
- Rapport: `Engagement` ou `Exploration` avec conversion formulaire contact.
- Periode identique a GSC (7 jours).
- Repartition par landing page (URL) si possible.

## Lecture hebdo rapide
- Priorite 1: pages avec fortes impressions + CTR bas -> retravailler title/meta.
- Priorite 2: pages avec clics mais faible conversion -> retravailler CTA et maillage vers `/contact`.
- Priorite 3: pages avec position qui baisse -> renforcer liens internes + mise a jour contenu.
