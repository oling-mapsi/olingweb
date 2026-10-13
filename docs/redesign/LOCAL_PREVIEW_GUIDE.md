# OLING.fr — voir et valider la refonte graphique locale (PR #1)

Date : 2026-10-13
Branche : `design/public-secondary-foundation-2026-10-13`
Production : **ne pas déployer / ne pas fusionner sans validation**.

## Où est le code ?
- PR en brouillon : https://github.com/oling-mapsi/olingweb/pull/1
- Sur GitHub, onglet **Files changed** : différences de code et de textes (ce n'est **pas** un aperçu du site).
- Brancher Codex sur un clone local existant d'OLING Web permet de voir de vrais rendus Symfony et de prendre des captures.

## Démarrage local isolé (Mac / Codex)

**Préconditions :** dépôt local OLING Web, PHP+extensions requises, Node, npm, Composer, base locale / de recette et configuration `.env.local` fonctionnelle. Ne pas utiliser une base de production en écriture.

1. Demander à Codex d'identifier le dépôt réel, de vérifier `git status` puis de préserver tout travail local non commité.
2. Récupérer la branche distante :

   ```sh
   git fetch origin design/public-secondary-foundation-2026-10-13
   ```

3. Créer un **worktree séparé** (chemin et branche locale à adapter si déjà utilisés), sans changer la branche du worktree principal :

   ```sh
   git worktree add -b review/oling-public-design ../olingw-public-design origin/design/public-secondary-foundation-2026-10-13
   cd ../olingw-public-design
   ```

4. Réutiliser uniquement la **configuration locale ou de recette** nécessaire depuis l'installation existante. Éviter toute copie de secrets dans des captures, des rapports, des commits ou les logs partagés. Ne pas pointer les commandes de migration vers la production.
5. Installer / réutiliser les dépendances si besoin, puis compiler :

   ```sh
   npm ci
   npm run build
   ```

   Avec un environnement PHP complet : `composer install`, puis tests PHP ciblés et Twig lint. Ne pas lancer de migrations ni de seed sur la production.

6. Lancer le serveur Symfony dans un terminal séparé, par exemple :

   ```sh
   php -S 127.0.0.1:8753 -t public tests/e2e/php-router.php
   ```

   Adresse de visualisation : http://127.0.0.1:8753

7. Vérifier dans Chrome/Safari :

   - Page d'accueil (référence graphique inchangée) : http://127.0.0.1:8753/
   - Qui sommes-nous : http://127.0.0.1:8753/a-propos
   - Équipe OLING : http://127.0.0.1:8753/a-propos/team
   - EN : `/en/about`, `/en/team`
   - ES : `/es/quienes-somos`, `/es/equipo`
   - Pages secondaires : `/expertises`, `/services`, `/secteurs`, `/projets`, `/ressources`, `/contact`

8. Audit browser réellement exécuté :

   ```sh
   npx playwright install chromium
   OLING_AUDIT_BASE_URL=http://127.0.0.1:8753 npm run audit:public-visual
   ```

   Le script contrôle 13 routes sur 7 largeurs, les styles calculés, les débordements, les balises SEO, les portraits, les données structurées et les menus mobiles.

   Captures PNG : `docs/redesign/screenshots-public-design/`.

   Audit JSON/Markdown : `docs/redesign/PUBLIC_THEME_AUDIT.json` et `docs/redesign/PUBLIC_THEME_AUDIT.md`.

   Un résultat non-PASS doit être corrigé avant validation. Les captures sont **générées uniquement si le navigateur a vraiment chargé les pages** ; aucune capture fictive.

## Comparaison graphique et SEO main vs refonte

- Faire d'abord des **captures de référence main** sur une installation séparée avec la même base locale, le même contenu et les mêmes viewports.
- Le script d'audit moderne peut être exécuté dans le worktree main en copiant temporairement le script (sans le commiter) avec `OLING_AUDIT_CAPTURE_BASELINE=1` ; conserver son JSON hors des deux worktrees.
- Sur la branche de refonte, fournir `OLING_AUDIT_BASELINE_FILE=/chemin/absolu/baseline.json`.
- Ne jamais comparer la prod courante à une recette dont les données n'ont pas le même état sans noter cet écart.
- Le remplacement de la liste de sept personnes par deux et le nouveau H1 de la page équipe sont des modifications **éditoriales volontairement SEO-sensibles** ; comparer et qualifier séparément les différences d'indexation / JSON-LD.
- Vérifier la différence de rendu sur 1920, 1440, 1366, 1024, 768, 390 et 360px, ainsi que menus, langues, formulaires, cookies et chat.

## Seuils de décision
- **VALIDATION GRAPHIQUE** : OK uniquement après consultation des captures et du navigateur réel.
- **VALIDATION TECHNIQUE** : build, PHPUnit, routes, sécurité et menus PASS.
- **VALIDATION SEO** : métadonnées, URLs, canonicals, hreflang, robots, sitemap et JSON-LD documentés, avec exceptions éditoriales explicitement acceptées.
- **PRODUCTION** : aucune fusion ni déploiement sans accord exprès du responsable OLING.

## Photos de la direction
Les photos réelles présentes dans le dépôt font 180×180 px. Les nouvelles cartes ne les agrandissent pas au-delà de 180 px afin d'éviter un rendu flou. Ajouter des portraits approuvés en haute définition avant d'envisager leur affichage en grand.
