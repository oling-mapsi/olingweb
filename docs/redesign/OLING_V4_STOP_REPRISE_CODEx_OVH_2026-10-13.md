# OLING Web — Procédure de reprise après STOP local et OVH (13 octobre 2026)

## État réel transmis par Codex
- Version GitHub / worktree local : `7d9a1d60cd343e1c4c82a369dbd03b49af1a70c6`.
- Production OVH : `9635f891a119fcb0e041219b9d10faffe5509de4`.
- Adresse locale testée : `http://127.0.0.1:8755`.
- Worktree de recette : `/Users/florestanrouet/.codex/worktrees/oling-prod-validate/olingw`.
- Fichiers de recette : `docs/redesign/PUBLIC_THEME_AUDIT.md`, `.json` et `preview.html`.
- **41 échecs locaux**, principalement signalés : quatre meta descriptions institutionnelles EN/ES manquantes, débordement à 1024px, sélecteur hors viewport à 1024px sur l'accueil.
- Connexion `ssh ovh-vps` = utilisateur `ubuntu` (et non `deploy`), `git fetch` refuse l'écriture de `.git/FETCH_HEAD`, deux sitemaps EN/ES non suivis.
- Aucune mise à jour ni migration n'a été effectuée sur OVH.

## Correctif GitHub en attente de recette
Branche : `hotfix/oling-v4-preprod-1024-seo-20261013`, PR n° 2 en brouillon.
- Navbar tablette repliée entre 992 et 1199px, langue accessible dans le menu ouvert, hauteur et débordements inspectables.
- Pour About/Team EN/ES, descriptions SEO de repli **strictement issues** des traductions versionnées `data/i18n/waves/site_pages.wave1.en.json` et `.es.json`, uniquement si la valeur CMS locale publiée ET la valeur de son payload structuré sont absentes ; jamais d'écrasement ni d'écriture en DB.
- Les deux sitemaps générés sont explicitement ignorés par Git sans suppression.
- Le workflow Chromium teste désormais 390, 768, **1024**, 1366 et 1920 px, y compris la page d'accueil.
- Les tests unitaires vérifient la priorité des contenus CMS et l'exactitude des quatre descriptions.

## Gate 1 — Reprise dans Codex sur Mac
1. Ne pas modifier le worktree initial. `git fetch origin` depuis le dépôt local puis utiliser **un worktree isolé** sur la branche PR n° 2. Préserver `.env.local` et la base **locales** ; ne jamais réutiliser la base CI à la place des données locales.
2. Si `npm ci` est disponible, l'utiliser pour une installation reproductible ; `npm run build`. Vérifier le nouveau manifeste compilé, Twig lint et PHPUnit ciblé.
3. Démarrer Symfony sur un port local libre. Lancer :
   ```sh
   OLING_AUDIT_BASE_URL=http://127.0.0.1:8755 npm run audit:public-visual
   ```
4. **Lire et catégoriser les erreurs réelles** dans `docs/redesign/PUBLIC_THEME_AUDIT.json`. La CI seule n'explique pas les 41 échecs avec les vraies données locales : joindre la liste intégrale au rapport, sans en déduire des causes non prouvées.
5. Gate strict : zéro erreur P0 à 1024px, `description` et `og:description` renseignés sur les 4 pages EN/ES, menu FR/EN/ES utilisable au clavier, accueil et pages secondaires sans défilement horizontal.
6. Aucun merge/deploy tant que la recette locale n'est pas entièrement comprise et PASS.

## Gate 2 — OVH : diagnostics **strictement en lecture seule**

Exécuter depuis Mac :

```sh
ssh ovh-vps '
  set -eu
  echo "IDENTITY"; id
  echo "DIRECTORY"; ls -ld /var/www/oling.fr /var/www/oling.fr/.git
  echo "FETCH_HEAD"; stat -c "%U:%G %a %n" /var/www/oling.fr/.git/FETCH_HEAD 2>/dev/null || true
  echo "GIT_DIR"; stat -c "%U:%G %a %n" /var/www/oling.fr/.git /var/www/oling.fr/.git/objects /var/www/oling.fr/.git/refs
  echo "GIT_HEAD"; git -C /var/www/oling.fr rev-parse HEAD
  echo "GIT_STATUS"; git -C /var/www/oling.fr status --porcelain=v1
  echo "DEPLOY_ACCOUNT"; getent passwd deploy || true
'
```

**Ne pas afficher les contenus de fichiers d'identifiants, les URLs Git contenant des tokens, les clés ou secrets d'environnement dans le rapport.**

Interprétation : le fait de se connecter sous `ubuntu` n'est **pas** une erreur en soi. Identifier le **propriétaire du dépôt** (et la procédure de déploiement réellement en place) avant de décider quel compte doit faire `git fetch`.

- Si le compte `deploy` existe ET possède le dépôt et ses droits SSH Git, envisager une opération sous `sudo -u deploy -H`, **après confirmation**.
- Si `.git/FETCH_HEAD` appartient à un autre utilisateur ou n'est pas accessible, documenter le cas avant toute correction **limitée et explicitement autorisée** ; surtout ne pas exécuter `chmod -R`, `chown -R`, `git reset --hard` ni `git clean`.
- Les fichiers `public/sitemap.en.xml` et `public/sitemap.es.xml` sont des fichiers **générés potentiellement légitimes** : les conserver. Leur présence seule n'empêche pas habituellement `git pull`; vérifier les collisions éventuelles avec des chemins suivis.

## Gate 3 — Merge et déploiement
**Ne s'applique qu'après Gate 1 PASS et Gate 2 résolu.**

Puis seulement : fusion contrôlée de PR n° 2, inclusion des assets CSS/JS hachés, fast-forward sur Mac/OVH depuis le compte Git identifié, cache Symfony clear/warmup, smoke tests site EN/ES et SEO, rollback préparé.

Référence : `docs/redesign/OLING_V4_RELEASE_DEPLOYMENT_RUNBOOK_2026-10-13.md`.

**La publication GitHub n'est pas la mise en production du VPS. Ne pas annoncer DEPLOYED tant que les validations live n'ont pas passé.**
