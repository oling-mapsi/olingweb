# OLING.FR — Mise en production contrôlée de la refonte V4
Date : 2026-10-13

## Architecture et accès précédemment confirmés
- Dépôt : `oling-mapsi/olingweb`, branche publiée `main`.
- Clone Mac : `/Users/florestanrouet/myweb/olingw`.
- Serveur OVH : alias SSH **`ovh-vps`**, projet Symfony **`/var/www/oling.fr`**, accès Git via utilisateur **`deploy`**.
- Apache / PHP / MySQL, DocumentRoot `/var/www/oling.fr/public`.
- Référence de production **à relever obligatoirement par SSH immédiatement avant toute modification**. L'ancien SHA GitHub main avant fusion est `9635f891a119fcb0e041219b9d10faffe5509de4`, ce qui **ne prouve pas** le SHA actuellement déployé.
- **Pas de migration, pas de seed, pas d'UPDATE SQL, pas de changement DNS/vhost/HTTPS.**
- Les binaires Webpack versionnés dans `public/build/` doivent être inclus dans le commit de sortie. Sans eux, `git pull` ne déploie pas effectivement le nouveau thème.

## Gate 1 — Recette locale sur Mac (Codex)
1. Identifier et protéger l'état Git de `/Users/florestanrouet/myweb/olingw`. Ne pas faire de `git reset --hard`, `git clean`, ou `git stash` opportuniste sur ce worktree historique.
2. `git fetch origin` et comparer `origin/main` à la release certifiée. Travailler dans un **worktree séparé**, par exemple `../olingw-design-v4-validation`, en utilisant `git worktree add --detach ... origin/main`.
3. Avec `.env.local` et base **locales seulement**, installer/contrôler les dépendances, lancer les tests PHPUnit ciblés, lint Twig, `npm ci`, et `npm run build`.
4. Comparer les assets rebuild aux `public/build/` versionnés de la release. Toute divergence inattendue est un STOP : diagnostiquer avant de publier.
5. Ouvrir réellement en navigateur local : `/`, `/expertises`, `/ressources`, `/projets`, `/secteurs`, `/a-propos`, `/a-propos/team`, `/contact` et équivalents EN/ES ; vérifier menu et sélecteur langue, formulaires (sans soumission à la prod), filtres, images, chat, cookies.
6. Sauvegarder la preuve local : commit SHA, build SHA, rapport et captures.

## Gate 2 — Préflight serveur OVH
1. `ssh ovh-vps` et vérifier sous utilisateur deploy le répertoire `/var/www/oling.fr`, l'URL de remote, la branche `main`, le SHA HEAD **actuel**, et `git status --porcelain`.
2. S'il existe des fichiers suivis modifiés, du code non commité, une divergence de branche ou un historique Git non compatible avec un fast-forward, **STOP, aucune réinitialisation automatique**.
3. Relever versions PHP, dépendances Symfony et configuration prod sans divulguer de secrets. Vérifier l'espace disque, la disponibilité des sauvegardes et conserver une archive datée de `public/build`, ainsi que le SHA précédent.
4. Noter une référence HTTP/SEO de `https://oling.fr/`, `/expertises`, `/secteurs`, `/projets`, `/ressources`, `/a-propos/team` avant l'opération.
5. Vérifier explicitement que la release contient bien un nouveau `public/build/manifest.json`, `entrypoints.json` et des CSS/JS hachés. Aucun import de fixture CI vers la prod.

## Gate 3 — Déploiement Git contrôlé
Exécuter depuis l'environnement Mac doté de l'alias SSH, uniquement si les gates 1 et 2 sont verts :
- Sur le serveur, sous `deploy`, depuis `/var/www/oling.fr` : `git fetch origin` puis `git pull --ff-only origin main`.
- Comparer HEAD au SHA de la release, **et** vérifier le manifeste CSS/JS construit inclus dans ce commit.
- Comme le lot ne touche pas `composer.json` ni `composer.lock`, ne pas imposer de migration ni de mise à jour des dépendances PHP. Si les prérequis du serveur le nécessitent, appliquer seulement la procédure de déploiement habituelle.
- `APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear --env=prod --no-debug` puis `cache:warmup --env=prod --no-debug`, sous l'utilisateur PHP/déploiement prévu.
- Vérifier les autorisations des fichiers `public/build`, du cache et de `var/log` sans modification destructive.

## Gate 4 — Vérification live immédiate
- HTTP 200 sur accueil, Expertises, Ressources, Projets, Secteurs, À propos, Équipe, Contact et versions EN/ES représentatives.
- Vérifier que le CSS haché chargé est celui du commit déployé, que la flèche langue a le bon contraste et que photos/filtres/forms/chat fonctionnent.
- SEO : `title`, `description`, canonical, hreflang, `robots`, JSON-LD, sitemap, redirections, pas de `noindex` imprévu. Réduction des personnes du JSON-LD Team (7 -> 2) **est un changement éditorial explicite**, pas une neutralité SEO.
- Un défaut critique (500, sécurité, accessibilité bloquée, assets non chargés, erreur de routage majeure) = **STOP et retour arrière contrôlé**.

## Rollback sans perte
- Ne jamais faire de `git reset --hard` ou `git clean -fd` en production sur un répertoire dont l'état n'est pas parfaitement maîtrisé.
- Préparer un **commit Git de revert** de la release à partir du SHA précédent, incluant le rétablissement des ressources compilées versionnées ; le pousser après contrôle, puis appliquer `git pull --ff-only` sous `deploy`, et cache clear/warmup.
- Sauvegardes/rollback DB : aucun changement de schéma ni données prévu, donc pas de restauration DB nécessaire pour ce lot ; ne pas toucher aux données client.
- Documenter le SHA avant/après, l'heure et les URL testées.

## Validation connue au 13 octobre
- [CI PHP/JS](https://github.com/oling-mapsi/olingweb/actions/runs/38430574024) : **40 tests / 1 191 assertions PASS**.
- [CI Chromium / SEO](https://github.com/oling-mapsi/olingweb/actions/runs/38430574153) : **48 parcours PASS**, **12 routes × 7 champs SEO inchangés**, sauf 6 deltas éditoriaux Team identifiés.
- Cette validation a utilisé une **base CI jetable** et ne remplace pas le gate local Mac / production.

Le merge GitHub est la **publication du code source**, pas une preuve de déploiement sur `oling.fr`.
