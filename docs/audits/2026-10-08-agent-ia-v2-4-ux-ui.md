# OLING.FR — Agent IA commercial V2.4 UX/UI

Date : 2026-10-08

## 1. État initial du widget

- Largeur desktop trop limitée : environ 448 px, lecture contrainte pour les réponses longues.
- Texte assistant insuffisamment cadré : héritage typographique global, contraste perfectible, paragraphes/listes peu respirants.
- Sources trop visuelles et verbeuses : cartes avec placeholders, descriptions répétables, lecture moins compacte.
- CTA V2.3 fonctionnel mais améliorable en visibilité et zone cliquable.

## 2. Modifications effectuées

- Ajustements limités à la présentation du widget, au rendu des sources et aux tests E2E.
- Aucune modification du prompt système, des providers LLM, de la qualification commerciale, de la structure des leads ou de l’action `open_lead_form`.
- Conservation du bouton `Transmettre mon projet à OLING`, du préremplissage formulaire, des liens `tel:` et `mailto:`.

## 3. Largeurs et règles responsive

- Desktop : `clamp(480px, 34vw, 540px)`, avec `max-width: calc(100vw - 2rem)`.
- Hauteur : jusqu’à `90vh/90dvh`, bornée par le bandeau cookies.
- Mobile : panneau plein écran contenu dans les marges de sécurité existantes, sans débordement horizontal.
- Contrôle Playwright : aucun overflow horizontal détecté sur 1920x1080, 1366x768, 768x1024, 390x844.

## 4. Palette de couleurs retenue

- Texte principal : `#172438`.
- Texte secondaire : `#455468`.
- Titres : `#142238`.
- Fond assistant : `#ffffff`.
- Fond secondaire : `#F5F7FA`.
- Bordures : `#DCE3EA`.
- Liens OLING : `#1F5F99`.

## 5. Améliorations typographiques

- Corps assistant verrouillé à `15px`.
- Interligne assistant : `1.55`.
- Paragraphes, listes, liens et emphases mieux espacés.
- Message visiteur conservé distinct, avec fond secondaire et largeur maîtrisée.

## 6. Présentation des sources

- Ajout du groupe `Sources utiles`.
- Suppression du média/placeholder dans les sources inline.
- Aucun `127.0.0.1` ni URL technique visible.
- Extraits raccourcis à 118 caractères et descriptions redondantes masquées.
- Liens sources conservés et cliquables.

## 7. CTA et formulaire

- CTA assistant renforcé : hauteur minimale, contraste, focus visible, zone de clic plus confortable.
- `open_lead_form` conservé.
- Formulaire prérempli V2.3 inchangé : le visiteur valide explicitement et coche le consentement RGPD.
- Téléphone rendu en `tel:0189701560`, email en `mailto:contact@oling.fr`.
- `/contact?chat_fallback=1` reste absent du texte visible.

## 8. Captures desktop et mobile

- Desktop 1920 : `test-results/chat-v2-4-chromium-1920.png`
- Desktop 1366 : `test-results/chat-v2-4-chromium-1366.png`
- Tablette 768 : `test-results/chat-v2-4-chromium-tablet.png`
- Mobile 390 : `test-results/chat-v2-4-chromium-mobile.png`

## 9. Résultats Playwright

Commande : `npm run test:e2e:chat`

Résultat : `8 passed`.

Couverture :

- Maison&Objet : CTA, formulaire prérempli, consentement, soumission.
- Sage X3 : réponse longue, titres, listes, sources compactes, liens, CTA, responsive.
- Résolutions : 1920x1080, 1366x768, 768x1024, 390x844.

## 10. Tests de non-régression

- `node --check assets/js/chat-widget.js` : OK.
- `php -l tests/e2e/php-router.php` : OK.
- `npm run build` : OK.
- `npm run test:e2e:chat` : OK, 8 tests passés.

Note : `axe-core` n’est pas installé dans le projet ; aucun contrôle axe automatisé n’a été ajouté pour éviter une dépendance non demandée. Les contrôles Playwright vérifient néanmoins contraste calculé, taille de texte, débordement, CTA, liens et rendu.

## 11. Problèmes résiduels

- Le build signale uniquement `Browserslist: caniuse-lite is outdated`, sans échec.
- Les captures sont des captures après V2.4 ; l’état initial est documenté par inspection du CSS précédent.

## 12. Décision GO/NO-GO UX

GO UX local.

La V2.4 améliore la largeur desktop, la lisibilité, le contraste, les sources, le CTA et le responsive sans modifier la logique commerciale validée en V2.3.
