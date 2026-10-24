# Qualification locale après accord utilisateur — 24 octobre 2026

Worktree `/Users/florestanrouet/myweb/olingw-ai`, branche `codex/ai-control-local`. Vérification initiale : aucun écart avec les empreintes du manifeste du deuxième cycle. Les changements préexistants ont été conservés.

## Test application : réussi sur fixtures

Le port 8094 était libre. Commande exécutée après accord explicite :

```sh
AI_TEST_VENDOR=/Users/florestanrouet/myweb/olingw-crm/vendor php -S 127.0.0.1:8094 scripts/ai/local-router.php
```

Le serveur créé, PID 24112, écoutait uniquement sur `127.0.0.1:8094`. Le bootstrap n'a chargé aucun `.env` ; base exclusivement `var/ai-fixture.sqlite`, déjà créée par ce lot. Aucun compte réel utilisé. Un second compte fictif `ROLE_USER` a été ajouté dans cette fixture pour vérifier le refus non-admin ; sa création fait désormais partie de `local-fixture.php` pour les recettes futures.

Chrome, onglet créé pour la tâche, sur `http://ai.localhost:8094` : connexion administrateur fictif, quatre vues Aujourd'hui/Missions/Assets/Prompts, création de « Recette locale — mission synthétique », détail de mission, statut Mac hors ligne, déconnexion et refus du compte non-admin constatés. Aucun clic de lancement Codex, aucun relais activé. La bibliothèque d'assets était vide, ce qui est attendu sans exécution ; téléchargement d'un vrai résultat non validé par cette recette.

Recette responsive sur une largeur de 390 px : navigation initialement comprimée ; correction CSS ciblée dans `templates/ai_control/base.html.twig` (navigation sur une ligne dédiée, espacements réduits). Après purge du seul cache dev du worktree et rechargement : largeur page = viewport = 390 px ; largeur navigation = contenu = 358 px. Les quatre entrées restent visibles. Captures fictives : [desktop](screenshots/mission-desktop.jpg), [mobile](screenshots/mission-mobile.jpg).

Contrôles HTTP avec `scripts/ai/local-http-smoke.py` : **22 réussis**, rejoués après correction. Connexion requise, quatre vues, cache privé/no-store et noindex, cookie de session sans Domain/HttpOnly/SameSite=Lax, refus du CSRF invalide, isolation des routes AI/CRM/site, session AI non acceptée par CRM/site même transmise explicitement dans le client fictif, relais refusé car désactivé, non-admin refusé et déconnexion. Connexions TCP fixées à 127.0.0.1 ; aucun suivi de redirection externe. Cookies et CSRF synthétiques sont restés en mémoire et ne figurent pas dans le rapport. Sur HTTP local, Secure n'est pas testé ; sa présence en HTTPS reste couverte par les tests PHP simulés. Résultats : [local-http-result.json](local-http-result.json).

Le navigateur intégré n'était pas disponible ; Chrome autorisé a été utilisé. Le premier appel HTTP depuis le shell était bloqué par le sandbox réseau ; l'appel élevé autorisé a réussi, sans refus d'approbation automatique. Aucun contournement d'un refus utilisateur. Le serveur a été arrêté par SIGTERM sur son seul PID : fin du processus confirmée (code 143) et aucun listener restant sur 8094. Déconnexion effectuée, onglet fermé, viewport restauré. Aucune prévisualisation persistante laissée active.

## Test Codex : autorisé, non exécuté faute de contrôle démontré

L'autorisation humaine des deux tests locaux est acquise. Ce blocage est technique, **pas une nouvelle demande d'accord ni un nouveau refus automatique**.

Inspection effectuée : aide du binaire officiel via `/Applications/ChatGPT.app/Contents/Resources/codex-cli/bin/codex app-server --help`, schéma public précédemment généré par `0.162.0-alpha.17.2`, documentation officielle actualisée. Aucun fichier d'authentification/configuration privée ou stockage de sessions lu. Aucun processus app-server de qualification ni `thread/start`/`turn/start` exécuté.

La [configuration officielle](https://learn.chatgpt.com/docs/config-file/config-reference) documente `features.shell_tool=false` pour les commandes, et des contrôles distincts pour images, apps, hooks, etc. Le [protocole officiel](https://learn.chatgpt.com/docs/app-server) ne fournit pas dans les interfaces inspectées un inventaire exhaustif des outils effectifs ou un sélecteur documenté garantissant zéro outil. Le schéma installé expose `experimentalFeature/list` (état des flags), mais `TurnStartParams` n'a ni `tool_choice`, ni liste blanche globale des outils. `permissions` est absent des paramètres de thread et de tour, contrairement à la documentation des profils plus récents. Cela ne prouve pas que le runtime est incapable de ces restrictions ; cela empêche ici d'attester leur application avant l'envoi du prompt.

**Point d'arrêt : avant tout lancement app-server et toute inférence.** Désactiver le shell et interrompre à réception d'une notification d'outil ne prouve pas qu'aucun outil n'a déjà agi. Aucun paramètre non documenté introduit, aucune protection supprimée. `assertRuntimeReady()` et le verrou relais sont inchangés. Le prompt synthétique `OLING_AI_ROUNDTRIP_42` n'a pas été envoyé. [probe-result.json](probe-result.json) enregistre explicitement `executed: false`, aucun identifiant de thread/tour et aucun succès inventé.

Pour reprendre ce point : établir un mécanisme officiel compatible avec le CLI installé permettant d'attester les outils effectifs et leur confinement avant inférence, puis le qualifier dans un script ponctuel revu. La connexion Codex peut être réutilisée selon l'accord acquis ; aucune nouvelle clé API n'est nécessairement requise et aucune n'a été créée. Aucune bascule API proposée par défaut.

## Vérifications et limites finales

- Après le correctif mobile : AI PHP **15 tests, 123 assertions réussis**, 6 Twig valides, syntaxe du script PHP fixture et du script Python valide, `git diff --check` sans erreur.
- Node **26 tests réussis** et CRM **1 test, 42 assertions réussis** : résultats du deuxième cycle précédent, non relancés pendant cette qualification ; code Node et sécurité inchangés.
- Aucun roundtrip Codex réel, parcours mission→Codex→revue→clôture, test de concurrence MySQL ou déploiement validé. Les tests HTTP locaux ne valent pas recette TLS/production.
- Aucune mutation OVH/DNS/production, aucun push, service permanent, nouveau credential réel ou changement global de sécurité.
