# Tracking SEO hebdo - mode d'emploi

## Fichier de suivi
- XLSX: `/Users/florestanrouet/myweb/olingw/output/spreadsheet/seo_tracking_hebdo_oling.xlsx`
- CSV: `/Users/florestanrouet/myweb/olingw/output/spreadsheet/seo_tracking_hebdo_oling.csv`
- Backlinks locaux (etape 8): `/Users/florestanrouet/myweb/olingw/output/spreadsheet/seo_backlinks_locaux_step8.csv`
- Outreach backlinks (etape 9): `/Users/florestanrouet/myweb/olingw/output/spreadsheet/seo_outreach_step9_pipeline.csv`
- Cross-links Oling/MAPSI (etape 10): `/Users/florestanrouet/myweb/olingw/output/spreadsheet/seo_crosslinks_mapsi_oling_step10.csv`

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

## Lecture hebdo backlinks (etape 8)
- Priorite 1: lignes `status=todo` avec `priority=P1` et `next_action_date` echue.
- Priorite 2: relancer toutes les lignes `status=contacted` > 7 jours sans reponse.
- Priorite 3: verifier les lignes `status=live` (lien actif, indexable, bonne URL cible).

## Lecture hebdo outreach (etape 9)
- Priorite 1: toutes les lignes `status=todo` dont la date de contact initial est due.
- Priorite 2: executer les relances J+4, J+10, J+21 selon `follow_up_1_date`, `follow_up_2_date`, `follow_up_3_date`.
- Priorite 3: toute ligne `status=live` doit avoir `qa_status` et `qa_score` renseignes.
- Priorite 4: basculer en `qa_approved` uniquement si la checklist est validee:
  - `/Users/florestanrouet/myweb/olingw/docs/seo-checklist-validation-lien-live-2026-06-03.md`

## Lecture hebdo cross-domain (etape 10)
- Priorite 1: verifier les lignes `direction=oling_to_mapsi` apres migration (`status=ready_after_migration` -> `live`).
- Priorite 2: executer les lignes `direction=mapsi_to_oling` cote MAPSI (publication ou demande CMS).
- Priorite 3: pour chaque lien live, verifier indexabilite et URL cible correcte.
