# Wrapper publié — mission réelle créée, association en attente

Date : 24 octobre 2026. Publication et enregistrement de la mission autorisés explicitement par l’utilisateur. Aucun nouveau credential, droit, service, OAuth/MCP, DNS, relais autonome ou changement de configuration.

## Publication vérifiée

- PR https://github.com/oling-mapsi/olingweb/pull/33 fusionnée après les deux CI réussies : PHP/build42s, recette visuelle/MySQL4m07s.
- Commit de l’overlay `bd415b349451fb42db88a2a3167193529f072db8` ; merge main `20cf028183fbe154781c7529daf7cfc3bb30d1e7`.
- Base fraîche PR31 `7a86077d0673e2089cacc116c18fbe9cb061d09c` et ancienne release `/var/www/releases/oling-crm-7a86077d0673` conservées. Aucun retour vers la release antérieure670447d.
- Release active `/var/www/releases/oling-ai-track-bd415b349451`. Exactement deux fichiers applicatifs : AiTrackCommand.php et AiNativeRegistry.php. Aucune migration.
- Paquet SHA256 `cde4204adb542f91dc93e147e88731f5b1209b1e206f5ba5676b6ff5c7f309d0` ; manifeste serveur `AI_TRACK_RELEASE.json` vérifié après bascule.
- Sauvegarde ciblée `/var/backups/oling/oling-ai-track-bd415b349451` : ancien registre copié et hash vérifié, marqueur d’absence de l’ancienne commande, liste des empreintes avant déploiement et pointeur précédent. Code seul modifié ; aucune restauration de base nécessaire et données conservées.
- Sous verrou partagé,549fichiers existants source/templates/config/publicCRM vérifiés inchangés hors le registreAI prévu ; stockage privé toujours `/var/www/ai-private`.
- Cache/conteneur, syntaxe PHP8.4 et disponibilité réelle de `app:ai:track --help` vérifiés. Bascule atomique, caches FPM renouvelés, Apache rechargé. Smokes serveur puis17contrôles HTTPS publics réussis sur OLING/CRM/AI ; certificat vérifié normalement, MCP503 et relais403.

Tests sur la base actualisée : AI27tests/274assertions, dont wrapper5/109 ; CRM55tests/660assertions sur14fichiers. Concurrence MySQL non qualifiée ; aucune proposition CRM déplacée.

## Mission réelle, pas une fixture

URL : https://ai.oling.fr/admin/ai/missions/0c1365ed78526eadbef15420d21f4fc3

Titre : Raccorder le suivi natif OLING IA. Propriétaire confirmé : florestan, ID2, administrateur. Vérification SQL restreinte à l’adresse exacte autorisée puis au compte/mission désignés, sans colonne de credential.

Le transport ponctuel SSH→commande Symfony comme www-data→registre a réellement été exécuté. La création `native-link-01a16e5d-create-v1` a été rejouée exactement : réponse identique, une seule mission. Le prompt initial générique limitait tout au local ; une version conforme au périmètre de publication explicitement autorisé a été conservée via `native-link-01a16e5d-prompt-v1`, sans effacer l’original. Lecture de contrôle par la commande et compteurs SQL concordants.

État actuel vérifié : **requested**, version2 ; **une mission, zéro run, zéro asset, zéro revue, deux événements** (`created`, `ai_mission_prompt`), aucune association. Pas de résultat ni de clôture simulés. Voir `native-cli-first-mission.json`.

## Blocage précis et suite

Action en attente, NON exécutée : `ai_task_associate` sur cette mission. Son contrat exige `model` et `effort` (énumération low/medium/high/xhigh/max/ultra). Le fil `01a16e5d-b400-71a8-87e7-134d4cb7b0c7` a été confirmé par l’outil natif read_thread, mais la réponse n’expose ni son modèle ni son effort. Aucun accès au stockage de sessions ou à l’application native refusée n’a été tenté.

Il faut soit fournir ces deux valeurs réellement établies, soit autoriser une représentation explicite « non renseigné » dans le contrat. Aucune valeur n’a été devinée et aucune modification de contrat supplémentaire n’est faite. Le statut final souhaité result_received et l’unicité d’un run/asset après rejeu du résultat **ne sont donc pas encore vérifiés en production**.

Après résolution : associer avec version2 et l’ID natif confirmé, rapporter started, déposer les vrais résultats et liens PR33/commit/release, rejouer exactement le résultat puis lire les compteurs. Clés prévues : native-link-01a16e5d-associate-v1, native-link-01a16e5d-started-v1, native-link-01a16e5d-result-v1. Utiliser les versions/run_id réellement retournés. Compter alors cinq événements au total, incluant la version de prompt déjà créée, sans revue ni clôture. Ne pas rejouer la création avec un brief différent.

Ce fonctionnement est une synchronisation ponctuelle réalisée par une tâche native autorisée, pas une synchronisation permanente ni une capacité du site à démarrer Codex.

## Retour arrière

Sous le verrou partagé, repointer atomiquement vers `/var/www/releases/oling-crm-7a86077d0673`, renouveler les caches PHP et recharger Apache. Conserver toutes les tables, la mission et les assets ; aucune migration down ni restauration DB globale. La release précédente reste compatible avec ces données.
