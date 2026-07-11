# Checklist SEO - Validation lien live

Date: 3 juin 2026

Usage:
- remplir cette checklist pour chaque opportunite avant passage en `qa_approved`;
- si un point bloquant est KO, conserver `status=live` et mettre `qa_status=fix_needed`.

## 1) Controles bloquants

- [ ] Source en `HTTP 200`.
- [ ] Page source indexable (`meta robots` sans `noindex`).
- [ ] Lien pointe vers la bonne URL Oling (pas de 404, pas de redirection incorrecte).
- [ ] Ancre conforme au mix (`brand` / `semi` / `exact`) defini en step 8.
- [ ] Environnement editorial pertinent (SI, AMOA, conformite, transformation, secteur cible).
- [ ] Domaine source non-spam (pas de reseau artificiel evident).

## 2) Controles qualite

- [ ] Lien `dofollow` (si `nofollow`, documenter la raison).
- [ ] Lien place dans un bloc visible du contenu.
- [ ] Page source mentionne la zone ou l'expertise ciblee.
- [ ] Date de publication renseignee.
- [ ] Capture d'ecran sauvegardee (preuve de mise en ligne).

## 3) Scoring QA

Noter de 0 a 2 chaque critere:
- Indexabilite
- Pertinence thematique
- Pertinence geographique
- Qualite domaine/source
- Coherence ancre + destination

Total /10:
- `>= 7` -> `qa_status=approved`
- `5-6` -> `qa_status=monitor`
- `< 5` -> `qa_status=reject`

## 4) Sortie obligatoire dans le CSV

Colonnes a remplir dans:
- `/Users/florestanrouet/myweb/olingw/output/spreadsheet/seo_outreach_step9_pipeline.csv`

Valeurs minimales:
- `status` (`live` ou `qa_approved` / `qa_reject`)
- `link_url`
- `published_date`
- `qa_status`
- `qa_score`
- `notes` (motif rapide si reject/monitor)
