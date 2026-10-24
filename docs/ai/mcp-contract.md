# Contrat de suivi OLING IA — séparé du CRM

État : implémentation métier locale testée ; transport vivant **non connecté**. Endpoint prévu `https://ai.oling.fr/api/ai/mcp`, JSON-RPC MCP 2025-03-26 via POST. `AiTrackingAccess` est volontairement indisponible : 503 sans lecture de credential. Aucune API ne permet à Symfony d’invoquer les outils privés de la plateforme.

| Opération | Scope | Effet |
|---|---|---|
| ai_missions_list | missions:read | Liste paginée du propriétaire (50 maximum) |
| ai_mission_get | missions:read | Détail, prompts, événements et preuves du propriétaire |
| ai_mission_create | missions:write | Demande, sans exécution |
| ai_mission_prompt | missions:write | Version de cadrage |
| ai_task_associate | tasks:associate | Identifiant/URL de tâche native, modèle/effort déclarés, exécutant unique |
| ai_task_event | results:write | Démarrage/interruption/arrêt déclaré par cet exécutant |
| ai_task_result | results:write | Résultat structuré, liens, contrôles déclarés non vérifiés indépendamment |
| ai_mission_review | reviews:write | Revue externe, identifiant/modèle/effort du reviewer, correction/arbitrage/validation proposée |

Les schémas JSON complets sont dans `mcp-tools.json`. Les opérations de mutation ont `event_id` (12–80 caractères) ; après création, `mission_id` et `version` attendue sont requis. Le rejeu strict renvoie le reçu sans nouvelle action. Même événement avec contenu différent, version périmée, autre exécutant ou transition interdite : conflit. La clôture définitive est une décision humaine CSRF dans l’interface.

Limites : corps HTTP 64 KiB ; titre180, brief20000, prompt30000, résumé20000 caractères ; résultat sérialisé60 KiB ; 20 liens et50 contrôles au plus. Pas de HTML rendu, scripts ni URLs arbitraires ; liens HTTPS GitHub/ChatGPT ou tâche `codex://threads/…` sans query/fragment/auth. Détection de motifs courants de secrets ; aucune promesse de détection universelle. Pas de lecture de fichiers générique, shell, SSH, lancement Codex, outil commercial ou mutation CRM.

HTTP : 503 non configuré, 401 identité invalide, 403 origine/HTTPS/administrateur, 405 méthode, 413 taille, 415 format. JSON-RPC : -32600 requête, -32601 méthode, -32602 arguments, -32003 scope, -32004 introuvable (inclut autre propriétaire), -32009 conflit. Notifications sans ID :202, aucune mutation. Liste des outils filtrée par scopes.

## Raccordement restant, avec autorisation distincte

La voie documentée du client est OAuth2.1 authorization code + PKCE S256, découverte du serveur d’autorisation et métadonnées de ressource protégée. Un simple Bearer à coller ou client_credentials ne suffit pas. Références officielles : https://developers.openai.com/plugins/build/auth et https://developers.openai.com/plugins/deploy/connect-chatgpt .

Aucun provider OAuth entrant réutilisable identifié dans composer/config/src. Le MCP CRM utilise son propre mécanisme Bearer spécialisé ; ses permissions ne doivent pas être élargies. Les clients OAuth sortants du dépôt ne sont pas un serveur d’autorisation.

1. Choisir un provider OAuth existant supporté et le lien explicite du subject à `app_user` administrateur. Définir issuer/audience/resource, scopes ci-dessus, durée et révocation. Ne pas construire un second serveur OAuth artisanal.
2. Adapter `AiTrackingAccess` : vérifier signature/issuer/audience/expiry/scopes puis fournir owner et actor stables ; aucun fallback cookie/clé CRM. Ajouter metadata resource/auth et challenge WWW-Authenticate cohérents. Les tests injectent une identité FAUSSE, pas une implémentation OAuth.
3. Après accord : enregistrer le client avec les callbacks réellement fournis par la plateforme, installer le plugin privé et consentir aux scopes. Aucun callback ni credential préfabriqué dans ce lot.
4. Vérifier la présence des outils dans le fil utilisateur puis une mission synthétique suivie de bout en bout. Déployer l’interface seul ne réalise aucune de ces étapes.

Alternative à étudier séparément : tâche native explicitement autorisée synchronisant uniquement ce contrat via un transport borné approuvé. Le script `native-fixture-flow.php` est une recette locale uniquement ; il n’est pas un connecteur de production. Aucune synchronisation automatique ni export manuel de prompt n’est présentée comme acquis.
