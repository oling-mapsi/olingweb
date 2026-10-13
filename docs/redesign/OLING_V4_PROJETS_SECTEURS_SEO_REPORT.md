# OLING.fr — lot de finition V4, compte rendu de recette

Date : 13 octobre 2026
Branche : `design/public-secondary-foundation-2026-10-13`
Pull Request : https://github.com/oling-mapsi/olingweb/pull/1
Environnement de recette : **GitHub Actions, MySQL jetable, contenus publics de démonstration et fichiers déjà présents dans le dépôt**.
Production : **non modifiée**.

## 1. Secteurs : illustrations réelles et cartes compactes

Observation initiale (capture V3) : cartes uniformes, grands pavés graphiques sans photographies sur la base de recette, nombreux « Références publiées : 0 », densité et blancs irréguliers.

Correctifs :

- `PublicSitePageResolver::getSectorCatalogEntries()` continue de respecter prioritairement l'image hero ou miniature choisie par le CMS.
- Si les deux champs sont vides, un catalogue **explicite** de 11 photos déjà versionnées sous `public/uploads/metiers/hero/` fournit le visuel correspondant au slug secteur : banque, eau/assainissement, collectivités, industrie, formation, mutuelles, négoce/distribution, santé/médico-social, transport, CCI, aménagement.
- Pas de téléchargement, génération d'image ou publication d'asset.
- La carte affiche désormais « Missions associées : N » uniquement si `N > 0`. Ce n'est pas une affirmation non vérifiée que chaque mission est « publiée » : le compteur provient de la relation `Metier::getProjets()`.
- Composant photographique au ratio 16/9, meilleurs recadrages, typographie et liste de spécificités resserrées.
- Contrôle PHPUnit des 11 fichiers : existence, largeur/hauteur minimales et chemins locaux. Contrôle Chromium de la présence et du chargement effectif des photos, sans erreurs.

### Limite

La présence d'une photographie dans le dépôt n'est pas une vérification de ses droits de diffusion ; ces fichiers étaient déjà versionnés sur OLING Web. Vérifier leur validation éditoriale avant fusion.

## 2. Projets : moins de densité, contenus conservés

- Les 12 missions de la fixture conservent intégralement contexte, intervention, périmètre, livrables, systèmes, liens et notes.
- Les anciennes synthèses longues sont maintenant dans des `<details>` HTML natifs : initialement fermés, consultables au clic et au clavier, et toujours présents dans le DOM. Leur visibilité initiale évolue, ce qui est à prendre en compte dans la revue éditoriale/SEO (il n'est **pas** garanti que les moteurs traitent un contenu replié à l'identique).
- Marges et typographies réajustées, badges compacts, interactions des filtres inchangées.
- Le navigateur vérifie l'ouverture/fermeture des notes, leur présence et les filtres (catégorie puis retour « Toutes »).
- La capture de recette mobile Projets est passée d'environ 20 337 px (V3) à 17 305 px (V4), pour une même largeur de 390 px, soit une réduction de l'ordre de 15 % **sur les données CI**, pas une mesure du site en production.

## 3. Comparaison SEO réellement rendue

Le workflow utilise **la même base MySQL de recette** pour exécuter :

1. la version actuelle `origin/main`, serveur Symfony sur `127.0.0.1:8754` ;
2. la branche V4, serveur Symfony sur `127.0.0.1:8753`.

Aucune donnée prod, aucun script déployant ni écriture sur une instance publique.

Comparateur : `scripts/ci/compare-public-seo-snapshots.mjs`.
- 12 pages comparées à 1366 px.
- 7 propriétés de référencement comparées par page : title, description, canonical, robots, hreflang, og:title, og:description.
- **84/84 valeurs inchangées** ; **0 régression détectée** sur ce périmètre.
- **6 différences éditoriales délibérées** sur les pages Team FR/EN/ES : 3 H1 et 3 ensembles `Person` JSON-LD (7 -> 2). Les autres profils demeurent dans la base ; leur absence de la page équipe change néanmoins la surface crawlable.
- Les fichiers source et les métadonnées ont été comparés dans une fixture identique, ce qui **ne vaut pas** contrôle des liens entrants, performances LCP/CLS ni visibilité réelle Google/Claude/OpenAI.

Preuves :
- [CI code et PHPUnit PASS](https://github.com/oling-mapsi/olingweb/actions/runs/38430574024)
- [CI Chromium + SEO différentiel PASS](https://github.com/oling-mapsi/olingweb/actions/runs/38430574153)
- Artifact GitHub `oling-design-preview-gallery` : `preview.html`, 36 captures générales, 6 vues rapprochées langue, `PUBLIC_THEME_AUDIT.json`, `SEO_DIFF_REPORT.md`, `SEO_DIFF_REPORT.json`, baseline main.
- Recette Chromium : **48 URL × largeur**, 0 échec.

## 4. Photographies de direction

`florestan-oling.png` est limité à 180 × 180 px. Pour Dorothée, `dorothee-oling.jpg` fait 180 × 180 px. Un fichier `dorothee-oling.png` existe en 1920 × 1697 px, **mais sa prise de vue est différente** de l'image JPG actuelle. Il ne faut **pas** le substituer automatiquement comme portrait officiel sans identification et validation par OLING.

Les portraits actuels restent donc inchangés et affichés à une taille adaptée à leur résolution.

## 5. Étapes et limites de publication

- [x] Mise à niveau graphique du portfolio Projets et des cartes Secteurs
- [x] Photos sectorielles existantes en repli sans écraser les sélections CMS
- [x] Suppression du compteur nul
- [x] Recette fonctionnelle des filtres, synthèses et chargement des photographies sur fixture
- [x] Comparaison de 84 métadonnées identiques sur 12 routes
- [ ] Contrôle humain des vrais contenus de la production et des droits photographiques
- [ ] Revue visuelle globale, y compris performance et accessibilité complète
- [ ] Vérification des liens entrants vers les cinq anciens profils supprimés du rendu de la page équipe
- [ ] Accord éditorial sur les notes repliées et l'affichage de la direction
- [ ] Autorisation expresse de merge puis déploiement

**Ne pas fusionner ni déployer avant validation.**
