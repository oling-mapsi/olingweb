# Suivi par tâche native — commande ponctuelle

**Commande publiée ; correction des métadonnées informatives inconnues en cours de finalisation.** Aucun changement de compte, d’OAuth, de MCP, de service ou de relais. Le registre conserve les résultats ; il ne lance jamais Codex.

## Invocation et frontière de confiance

Commande Symfony : `app:ai:track`, obligatoirement non interactive (`--no-interaction`). Entrée JSON par stdin, maximum 65 536 octets, exactement deux propriétés :

```json
{"operation":"ai_missions_list","arguments":{"limit":25,"offset":0}}
```

Options fournies séparément par l’opérateur autorisé :

- `--owner-username` : identifiant exact confirmé par l’utilisateur. Lecture SQL limitée à id/username/roles ; compte unique et ROLE_ADMIN ou ROLE_SUPER_ADMIN requis. Pas d’email, hash ou mot de passe lu par le wrapper.
- `--actor-task-id` : UUID du fil pilote confirmé dans le contexte natif. L’acteur stocké est `native-<UUID>`, stable pour toutes les opérations d’une mission.
- `--native-task-id` : UUID de la tâche concernée, obligatoire pour associer une réalisation ou transmettre une revue, refusé pour les autres opérations. Doit correspondre exactement au champ task_id ou review_task_id du JSON.
- `--apply` : requis pour chaque mutation ; absent, l’opération est refusée sans écriture. Ce n’est pas un mode simulation de transitions.

Cette séparation empêche le contenu d’une mission de choisir l’identité d’exécution. Elle **ne transforme pas un UUID fourni en preuve d’existence**. Le pilote doit obtenir cet UUID de la plateforme native ou du contexte utilisateur fiable. La commande n’appelle aucune API de vérification ; sortie et journal d’association/revue portent `operator_declared_not_platform_verified`, et la sortie porte `native_task_verified: false`.

Authentification de transport proposée : l’accès SSH d’exploitation existant, utilisé ponctuellement par une tâche native explicitement autorisée. La commande ne crée pas une nouvelle frontière de sécurité autour de cet accès privilégié et ne change aucun droit SSH/sudo. Elle limite uniquement son propre contrat métier. Aucun paramètre propriétaire/acteur issu d’un prompt, fichier métier, résultat de modèle ou JSON entrant ne doit devenir une option d’invocation. Ne jamais construire une commande shell à partir du résultat d’une mission.

Après approbation du déploiement, invocation serveur préparée : processus borné, conteneur `oling-php84`, utilisateur `www-data`, répertoire `/var/www/oling-current`, APP_ENV=prod et APP_DEBUG=0 ; `php bin/console app:ai:track --no-debug --no-interaction ...`. Le pilote envoie le JSON directement sur stdin et consomme la réponse JSON. Aucun copier-coller utilisateur, token, session navigateur, endpoint HTTP activé ou daemon nécessaire. Ne pas utiliser cette procédure avant validation du parent.

## Opérations et limites

Allowlist fixe de huit opérations : `ai_missions_list`, `ai_mission_get`, `ai_mission_create`, `ai_mission_prompt`, `ai_task_associate`, `ai_task_event`, `ai_task_result`, `ai_mission_review`. Le wrapper réutilise les [schémas existants](mcp-tools.json). Il n’expose ni suppression, clôture humaine, SQL, fichier arbitraire, shell, action CRM ni lancement d’agent.

Les mutations ont event_id ; après création elles ont mission_id et version attendue. Les événements relatifs à une exécution utilisent aussi run_id. Rejeu identique : même reçu et aucun effet supplémentaire. Même event_id avec payload ou acteur modifié : conflit. Propriétaire, exécutant, transition, version et tâche courante contrôlés par AiNativeRegistry. L’identifiant de tâche doit être unique entre missions.

Limites inchangées : liste 50, titre 180 caractères, brief 20 000, prompt 30 000, résumé 20 000, résultat sérialisé 60 000 octets, 20 preuves, 50 contrôles. Liens restreints, motifs courants de secrets refusés, HTML traité comme texte. Les résultats restent des déclarations d’exécutant, pas une certification indépendante des tests. Deux corrections maximum puis arbitrage. `approve` externe prépare la validation ; la clôture reste une décision humaine avec CSRF dans l’interface.

Sortie : `{ok, identity_source, native_task_verified, task_reference_source, execution_triggered:false, result}`. Erreur : `{ok:false,error}` uniquement, sans payload, SQL ou trace, même en mode verbeux. Codes de sortie : 0 succès ; 1 indisponibilité ; 2 entrée/invocation invalide ou --apply manquant ; 3 propriétaire refusé/ressource introuvable ; 4 conflit/concurrence. Après une réponse incertaine, rejouer le même événement, jamais en inventer un nouveau.

## Première mission réelle proposée — aucune écriture effectuée

Titre : **Raccorder le suivi natif OLING IA**. Projet : `oling-ai`. Sujet : ce wrapper et sa recette, puis son raccordement ponctuel. Tâche native fournie par le parent : `01a16e5d-b400-71a8-87e7-134d4cb7b0c7`. Pilote proposé : `01a16e14-ad4d-71c7-a614-f31bf5a4f0d7`. Les deux références sont issues de l’instruction du parent, non d’un résultat de modèle ; elles restent marquées déclarées côté serveur.

Séquence proposée après approbation :

1. `ai_mission_create`, event_id `native-link-01a16e5d-create-v1`, titre ci-dessus, brief décrivant le lot réellement réalisé et ses critères, project `oling-ai`. Conserver mission_id/version retournés.
2. `ai_task_associate`, event_id `native-link-01a16e5d-associate-v1`, task_id ci-dessus, task_url `codex://threads/01a16e5d-b400-71a8-87e7-134d4cb7b0c7`, modèle/effort effectifs rapportés par le pilote, ou marqueur réservé `unknown` pour chaque valeur non exposée. Ces métadonnées ne sont jamais des paramètres d’exécution et ne sont pas vérifiées par la commande.
3. `ai_task_event`, event_id `native-link-01a16e5d-started-v1`, event `started`, run_id courant : suivi d’une tâche existante, aucun lancement ni prétention à connaître son horodatage initial.
4. `ai_task_result`, event_id `native-link-01a16e5d-result-v1`, bilan exact, vrais liens de commit/PR une fois publiés, contrôles réellement exécutés. Rapporter séparément ce qui n’est pas vérifié. Rejouer exactement cet événement pour constater le même reçu et un seul asset.
5. `ai_mission_get` : vérifier états, références et provenance dans le registre. État attendu à ce stade : `result_received`. Pas de fausse revue ni de clôture automatique. Une revue ultérieure exige un vrai reviewer et ses références.

Pour chaque étape, utiliser la dernière version/run_id renvoyée ; les IDs d’événements ci-dessus sont stables. Aucun placeholder de commit, résultat fictif, ID de test ou valeur de modèle supposée ne doit être envoyé en production. Le propriétaire exact a été rapproché en lecture seule et est transmis au parent séparément ; aucune donnée de compte n’est ajoutée à ce document versionné.

## Publication ciblée à approuver

Deux fichiers applicatifs seulement : `src/Command/AiTrackCommand.php` (nouveau) et `src/Service/Ai/AiNativeRegistry.php` (marqueurs de provenance ajoutés au journal). Aucune migration, donnée, configuration sécurité, dépendance ou modification UI.

Avant publication : relecture du diff et des tests, commit/PR/CI selon procédure existante, préflight de la release active. Sous verrou `/var/lock/oling-deploy-cms-p0.lock`, sauvegarde vérifiée et copie de la release courante ; vérifier le hash de l’ancien AiNativeRegistry et l’absence de la nouvelle commande avant overlay. Garder le stockage privé lié, appliquer les deux fichiers seulement, linter/cache avec PHP8.4 existant, bascule atomique, renouveler les caches PHP et smoke OLING/CRM/AI. Pas de réexécution de migration. Rollback par retour de symlink ; préserver tables et assets.

La publication et l’écriture de cette mission ont été explicitement autorisées. La mission existante est `0c1365ed78526eadbef15420d21f4fc3` : ne pas la recréer. Consulter son état et les dernières preuves avant reprise. Le MCP reste à503 et le relais reste refusé403.

## Métadonnées informatives non renseignées

Les colonnes `ai_run.model` et `ai_run.effort` sont NOT NULL (varchar100 et varchar16). Pour ne modifier ni le schéma ni les anciennes données, la valeur réservée `unknown` représente explicitement une métadonnée non fournie. Null, chaîne vide, types incorrects, longueurs dépassées et efforts non reconnus restent refusés. Les valeurs connues conservent leur validation existante.

Le journal d’association/revue et les lectures de run incluent une qualité dérivée : `source=operator_report`, `verification=not_independently_verified`, et pour chaque champ `not_provided` ou `reported`. Le client ne peut pas remplacer ce marqueur par une prétendue vérification. L’interface affiche « non renseigné » et indique qu’aucune valeur n’est déduite ; le marqueur ne désigne aucun modèle réellement exécuté. Aucun paramètre Codex n’est changé.

Cette correction porte uniquement sur AiTrackingContract, AiNativeRegistry et le template de mission. Aucune migration ni authentification modifiée. La reprise autorisée de la mission existante associera le fil réel avec model=unknown et effort=unknown, puis enregistrera le résultat réel et son rejeu avant lecture des compteurs, sans revue ni clôture.
