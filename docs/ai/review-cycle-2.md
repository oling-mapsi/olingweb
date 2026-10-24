# Deuxième cycle correctif — 24 octobre 2026

Branche `codex/ai-control-local`, worktree `olingw-ai`. Aucun appel Codex réel, serveur HTTP local, credential réel, changement global de permissions ou publication. Activation désactivée.

## P1 lectures du modèle : lancement fermé, garantie runtime non démontrée

La documentation officielle distingue les [profils de permissions](https://learn.chatgpt.com/docs/permissions), qui peuvent borner les lectures des commandes, de l'ancienne sandbox. Elle documente `:root = deny`, `:minimal = read` et les règles de workspace. Ces profils ne doivent pas être mélangés à `sandbox_mode`/`sandbox_workspace_write`. Le [protocole app-server actuel](https://learn.chatgpt.com/docs/app-server) décrit `permissions` à la place de `sandbox`, et `permissionProfile/list`.

Le schéma public déjà généré par le CLI installé `0.162.0-alpha.17.2` a été inspecté sans relancer le CLI : `permissionProfile/list` est présent, mais les propriétés `permissions` sont absentes de `ThreadStartParams` et `TurnStartParams`. Le `SandboxPolicy` legacy ne contient pas de racines de lecture. Source locale ignorée : `var/ai-schema/codex_app_server_protocol.schemas.json`, SHA-256 `c7338bc0830b61fb07ba35325d4fbeb8207a919e53468c664dda5803aacb6c8e`.

Ce constat ne démontre ni l'absence de tout support par configuration transitoire, ni son efficacité. Sans recette autorisée, la séparation entre l'accès propre du runtime à l'authentification existante et les lectures/outils accessibles au modèle reste inconnue. Aucun paramètre inventé, changement HOME/CODEX_HOME, lecture ou copie d'authentification, modification de configuration globale ou nouveau profil persistant.

**Mesure appliquée :** `assertRuntimeReady()` refuse inconditionnellement le client réel, le relais et la sonde, avant spawn et avant lecture de la configuration du relais, token, création de verrou ou claim. Aucun `enabled`, drapeau d'environnement ou option `certified` ne déverrouille cette révision. `AppServerProtocol` reste un moteur de protocole pour les tests factices ; les points d'entrée applicatifs utilisent le client bloqué. La sonde sans outils est également bloquée et non certifiée. Une autorisation utilisateur seule ne supprime pas ce verrou technique : il faudra un changement revu et une recette synthétique autorisée des permissions effectives.

Tests : refus constructeur avec et sans outils, fausse option de certification ignorée, relais refusé avant lecture d'un fichier de configuration inexistant ; configuration transmise au pair inspectée et environnement empoisonné fictif filtré. **Ce sont des preuves du refus applicatif et du protocole simulé, pas du confinement du CLI réel. P1 runtime restant pour arbitrage, exposition neutralisée par le refus.**

## P1 arrêt : sortie attendue, groupe dédié et escalade bornée

Chaque processus du moteur de protocole est créé dans un groupe POSIX dédié. `close()` est asynchrone et idempotent : stdin fermé, SIGTERM au groupe possédé, attente de sortie du processus et disparition du groupe ; puis SIGKILL après 1 s et attente limitée à 2 s. Une terminaison non confirmée échoue explicitement ; le relais conserve son verrou. Les chemins d'échec après refus attendent cet arrêt avant de rendre l'erreur. Les appelants attendent `close()` ; `process.killed` n'est plus utilisé comme preuve.

Tests OS exécutés sur le Mac avec des programmes Node factices uniquement : parent et enfant ignorant SIGTERM, escalade, absence des deux PID et du groupe, sortie réelle du parent, fermeture idempotente, groupe témoin étranger toujours vivant ; processus coopératif sans escalade. Délais raccourcis dans les fixtures. Aucune recherche ni terminaison d'un processus utilisateur existant.

Portée : descendants restant dans le groupe créé par ce spawn. Un descendant qui s'échapperait dans une nouvelle session n'est pas couvert ; aucun scan ni kill d'autres groupes n'est tenté. Le futur confinement runtime doit empêcher ou traiter ce cas avant activation. Windows échoue explicitement après arrêt du processus direct, sans prétendre certifier les descendants ; non testé ici. Ces limites empêchent de présenter l'arrêt d'un vrai runtime Codex comme validé.

## Interruption pendant l'exécution

Les trois scénarios passent d'abord le heartbeat initial, entrent réellement dans `client.run` factice, puis déclenchent au heartbeat suivant une annulation distante, une perte de connexion ou un signal local. Assertions : une seule exécution et réservation, signal reçu pendant le travail, résultat `interrupted` pour les annulations, aucune livraison lors de perte de heartbeat et journal `needs_reconciliation` interdisant le rejeu. Le test d'expiration pendant exécution reste présent.

## Résultats finaux du cycle

- Node : **26 tests réussis, 0 échec, 0 annulé, 0 ignoré** (11 protocole, 13 relais, 2 arrêt OS factice).
- AI PHP : **15 tests / 123 assertions réussis**.
- Régression CRM relancée sur cet état : **1 test / 42 assertions réussis**, suite séparée.
- Syntaxe : 16 fichiers PHP et 6 modules Node valides ; conteneur, 6 Twig et YAML sécurité valides ; `git diff --check` sans erreur.

Un premier passage Node a annulé les deux nouveaux tests d'arrêt : la fixture générée contenait une nouvelle ligne JavaScript mal échappée et ne démarrait pas. Fixture corrigée ; les deux tests isolés puis la suite complète sont passés. Aucun refus d'outil durant ce cycle.

Restent : relecture, preuve de confinement runtime et absence d'outils pour la sonde, approbation des deux actions réelles déjà refusées, aller-retour applicatif réel, recette navigateur, concurrence MySQL/MariaDB, connecteur entrant et accès/activation du relais approuvés séparément. Les tests SQLite ne valident pas MySQL. Aucun déploiement engagé.
