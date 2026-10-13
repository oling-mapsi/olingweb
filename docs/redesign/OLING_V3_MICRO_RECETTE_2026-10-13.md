# OLING.fr — Recette design V3.1, constats et critères d'acceptation
Date : 13/10/2026 · Branche : `design/public-secondary-foundation-2026-10-13`
Sources : captures Chromium réelles de la PR (base de recette versionnée), gabarits Twig et CSS ; pas de certification visuelle sur production.
Statut : correction CSS en branche, recette différentielle SEO et validation de mise en production non réalisées.

## 0. Règles générales et seuils de recette
- **Identité :** accueil sombre inchangé hors composant langue ; pages secondaires claires et éditoriales, mêmes tons ardoise/bleu/rose OLING, aucun composant « faux dashboard ».
- **Header :** bandeau pleine largeur ; menu fonctionnel à 360/390/768/1024/1366/1920 px ; globe/code pays/flèche lisibles au clavier et à la souris ; flèche bien visible dans le menu mobile.
- **Colonnes :** grille visible symétrique, **pas de carte étriquée sous une carte large** ; ratio entre cartes sœurs desktop >= 0,42 de la carte principale si agencement 1+2 ; gouttière 16–28 px, zéro débordement.
- **Éditorial :** sections alignées sur 1250 px maximum ; 24–48 px de respirations latérales, selon viewport ; sections centrées sur leur propre axe (tolérance 14 px).
- **SEO :** conserver URL, canonicals, hreflang, indexation et liens ; toute exception éditoriale sur Team doit rester identifiée ; comparer avant/après à contenu de recette égal.
- **Sémantique :** préserver les vrais H1/H2 et les données structurées visibles, contrastes WCAG, focus, formulaires et filtres.
- **Qualité :** captures en 390/768/1366/1920 px ; l'audit automatisé ne remplace jamais une lecture visuelle humaine.

## 1. Navigation et sélecteur de langue
**P0 — PASS recette Chromium isolée V3.1 (48 échantillons, 0 échec).**
Observation apportée par OLING : chevron blanc presque invisible sur la capsule gris clair.
Correction : capsule `#EEF1F5`, globe/FR/flèche `#18354C`, pseudo-élément tracé en CSS, cible de clic inchangée, focus visible.
Contrôles : clic FR/EN/ES, menu mobile ouvert, contraste pseudo-élément, captures proches du composant.
À vérifier sur la vraie production après validation : styles compilés et cache CDN, car les captures locales ne mettent pas oling.fr à jour.

## 2. Expertises — landing de conversion prioritaire
**P0 — PASS recette Chromium isolée V3.1, correction confirmée sur capture 1366 px.**
Constat sur capture V3 précédente : domaine 01 mesuré à ~1187 px de large sur 1366 px de viewport, domaines 02/03 dans des colonnes étroites ~380 px chacune, laissant une troisième colonne vide et cassant la lisibilité.
Correction V3.1 : grille 2 colonnes pour les domaines 02/03 avec largeur égale ; domaine 01 pleine largeur mais contenu découpé en tête + liste de services ; adaptation tablette/mobile. **Mesure réelle à 1366 px : domaine 01 = 1187,2 px ; domaines 02 et 03 = 579,9 px chacun, sans piste vide.**
Critères : les cartes 02/03 occupent chacune au moins 42 % de la largeur utile, titres non cassés artificiellement, liste des liens active, cartes non imbriquées, hauteur équilibrée. Ajouter une vérification navigateur explicite des largeurs.
**P1 — À affiner avec revue des captures.** Hiérarchie entre les trois domaines, les neuf expertises, les expertises complémentaires et MAPSI : transitions lisibles, densité de texte, sections bien différenciées ; CTA orientés action en haut et fin de page. Ne pas supprimer les contenus SEO pour réduire la hauteur.

## 3. Ressources
**P1 — PASS sur capture Chromium de recette V3.1 ; contrôle de production encore requis.**
Constat V3 : la zone « Expertises recherchées » est désormais centrée (offset de centre ≈ 0 px mesuré à 1366 et 390), mais se trouve entourée de deux contours blancs et de marges superflues ; en mobile, la zone de lecture mesurée n'est que ~250 px sur un viewport 390 px.
Correction : retrait de l'encadrement du conteneur extérieur, maintien d'un seul panneau intérieur ; structure CMS et six liens préservés. **Largeur mesurée du contenu à 390 px : 294 px (250 px auparavant)** ; centrage géométrique 0 px ; un seul contour visible sur la capture.
Critères : panneau centré, un seul contour, typographie 18–20 px en mobile pour titre, liens cliquables sans coupe, au moins 280 px de largeur intérieure sur 390 px de viewport ; grille des articles lisible en 3/2/1 colonnes ; liens RSS et FAQ inchangés.

## 4. Projets
**P1 — Traitement graphique réalisé, optimisation de densité à valider.**
Constat sur capture V3 : portfolio bien différencié visuellement en deux panneaux, mais longue page (capture mobile ~20 337 px pour 12 missions). Les rubriques Contexte/Intervention/Périmètre/Livrables peuvent paraître denses et répétitives.
Critères : filtre « Toutes » puis catégorie puis reset fonctionnel, aucune mission masquée par défaut, contenu et liens de chaque mission intégralement accessibles, titre/numéro/secteur repérables en 3 secondes, version mobile avec panneau d'identité puis contenu (aucun défilement horizontal). Ajuster les espacements et l'aération des puces sans cacher de texte aux moteurs.

## 5. Secteurs
**P1 — Cartes modernisées, revue de données et illustrations à prévoir.**
Constat V3 : neuf cartes verticales de type image + contenu ; sur les fixtures CI, nombreuses zones d'image sont des gradients sans photographie réelle et les cartes affichent « Références publiées : 0 ». Ces chiffres peuvent différer en production.
Critères : format media cohérent, crop uniforme, titres de secteurs réguliers, évitement du vide excessif entre spécificités et pied de carte, liens identifiables. **Éviter de mettre en avant un chiffre 0** dans une carte qui n'a aucune référence publiée, après validation éditoriale ; utiliser de véritables photos approuvées si des assets existent. Ne pas fabriquer de références.

## 6. À propos / Équipe
**P2 — Charte structurelle conforme, photographies insuffisantes pour un agrandissement.**
Deux profils de direction : Florestan Rouet et Dorothée Maitrias, les cinq autres restent en base ; portraits source 180×180 px. Conserver l'affichage à définition native ; demander des portraits HD approuvés pour enrichir la composition. Vérifier cohérence FR/EN/ES et correspondance JSON-LD `Person` avec les profils visibles.

## 7. Services / Contact / Pied de page
**P2 — Harmonisation engagée, vérifications fonctionnelles à poursuivre.**
Services : espacement des grilles et titres sur pages hub et détail, alternance sections blanches/teintées sans effet « mille cartes ». Contact : CTA, alignement intro/formulaire, labels, champs, consentement et messages d'erreur. Footer : cohérence typographique avec la nouvelle direction artistique sans rompre la hiérarchie des liens légaux.
Ne jamais soumettre de données à une instance de production pendant la recette.

## Blocage de publication
- [ ] Audit Chromium V3.1 sur les captures réellement produites et examinées
- [x] Vérification couleur/flèche de langue sur page et menu mobile dans Chromium CI isolé ; contrôler ensuite l'instance finale.
- [x] Domaine Expertises 02/03 mesuré et sans écrasement (579,9 px chacun à 1366 px).
- [x] Ressources sans double cadre, largeur mobile 294 px sur viewport 390 px.
- [ ] Vérification des 12 projets, 9 secteurs et filtres/formulaire
- [ ] Diff SEO avant/après et contrôle des textes localisés à données équivalentes
- [ ] Accord explicite du responsable OLING

La PR reste en **brouillon**, branche non fusionnée ; aucune mise en production.

## Preuves d'exécution V3.1
- Actions PHP/JS/build : https://github.com/oling-mapsi/olingweb/actions/runs/38425676544 — succès.
- Actions navigateur : https://github.com/oling-mapsi/olingweb/actions/runs/38425676390 — succès, **48 combinaisons / zéro échec**, 36 captures générales et 6 zooms du sélecteur de langue.
- ZIP : artifact `oling-design-preview-gallery` de ce run ; captures navigateur sur fixture MySQL versionnée, **pas production**.
- Sélecteur calculé : fond `rgb(238, 241, 245)`, chevron `rgb(24, 53, 76)`, trait 2 px, dimensions réelles 83 × 40 px à 1366 px.

## Point de micro-recette supplémentaire — mobile
Sur le zoom du sélecteur du menu mobile, le bord du lanceur de chat rose semble empiéter sur la zone du bouton de langue. Tester la navigation ouverte à 390 px avec le widget chargé. Si le chevauchement est confirmé, réduire/cacher temporairement **le lanceur seulement** pendant l'ouverture du menu, sans retirer le chat ni modifier son comportement hors menu. Ce point n'empêche pas l'arrow de s'afficher mais doit être réglé avant mise en production.
