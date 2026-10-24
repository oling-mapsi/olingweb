# Publication AI — V1 simple

Lot autorisé le24octobre2026 : interface HTTPS dédiée, identités OLING existantes, tables AI additives, sauvegarde et rollback. DNS traité par une autre tâche : A `ai.oling.fr`→`54.37.230.240`, TTL3600 confirmé. Aucun OAuth/plugin/relais/service autonome activé.

## Référence et manifeste exact

Base `876770b48e09e135b890ff617bc1a81b6ee4c730`, avec PR29. Le manifeste `manifest.json` énumère et hash tous les fichiers locaux ; `production_files` contient l’overlay seulement. Ne jamais déployer le worktree en bloc, les fixtures, `.env`, vendor local, sessions ou scripts de sonde/relais.

Deux versions à exécuter individuellement, dans l’ordre :

1. `DoctrineMigrations\Version20261024180000` : ai_mission, ai_prompt, ai_prompt_version, ai_run, ai_asset, ai_review, ai_event, ai_relay (table historique inactive).
2. `DoctrineMigrations\Version20261024220000` : ai_native_binding et ai_tracking_receipt.

Aucune migration globale, suppression de table, schema:update ou donnée métier modifiée. Les down refusent volontairement la destruction. Données AI nouvelles exclusivement ; aucune fixture publiée.

## Hébergement et séquence

Serveur existant `ovh-vps`/srv-web-oling54.37.230.240. Apache→FPM127.0.0.1:9084, conteneur oling-php84 PHP8.4.27. Pointeur `/var/www/oling-current`, préflight confirmé vers `/var/www/releases/oling-crm-876770b48e09`. Verrou non bloquant `/var/lock/oling-deploy-cms-p0.lock` pour toutes phases ; ne pas forcer un concurrent.

1. Commit/PR du lot et CI, préserver branche principale et contrôles ; vérifier l’état actif à nouveau.
2. `scripts/ai/deploy.py prepare <package> <commit>` : sauvegarde DB cohérente, restauration de contrôle en base temporaire isolée puis suppression de cette seule copie ; archive chiffrée root-only avec vérification déchiffrement/hash. Ancienne release conservée. Aucun contenu de sauvegarde imprimé.
3. Copier la release active, overlay AI autorisé, conserver les fichiers existants à l’identique. `patch-security.py` ajoute les trois contextes AI sans écraser le throttling et les autres protections de production. Ni identité ni mot de passe modifié. Stockage `/var/www/ai-private`,700 www-data, fichiers600, lien depuis var/ai-private ; hors public et présent dans le montage existant du conteneur.
4. Exécuter uniquement les deux migrations, vider/préchauffer le cache de la nouvelle release et linter via le PHP8.4 existant. Pas de Composer/install ni changement de dépendances.
5. Nouveau vhost80 AI : webroot ACME existant `/var/www/letsencrypt`, redirection HTTPS hors challenge. Certificat séparé `ai.oling.fr` par certbot webroot existant ; ne toucher aucun autre certificat. Puis vhost443 DocumentRoot `/var/www/oling-current/public`, configuration FPM et headers existants conservés. Session `OLINGAISESSID`,Secure/HttpOnly/SameSiteLax,host-only.
6. Validation Apache, bascule symlink atomique, signal USR2 au seul conteneurPHP pour caches, reload Apache. Smoke OLING/CRM/AI ; rollback immédiat de code si échec. Connecteur503 et relais403 attendus.

## Contrôles et rollback

HTTP public OLING/,login ; CRM login et redirection anonyme ; AIlogin, anonyme, routes croisées404, MCP503, relais403, certificat et cookies. Code des deux dropzones CRM conservé par hash et suite53tests. Les quatre vues et refus non-admin sont testés avec fixtures locales ; pas de création d’un compte réel pour tester en production. Vérification authentifiée réelle à faire par l’utilisateur si aucune session volontairement fournie.

En cas d’échec : conserver le verrou, repointer atomiquement vers le chemin dans `previous-release.txt`, USR2PHP/reloadApache. Désactiver seulement le nouveau vhostAI si nécessaire. Garder DNS/certificat/tableAI/assets ; aucune destruction automatique et aucun restore global de DB qui écraserait des modifications CRM intervenues depuis. La sauvegarde sert à une restauration délibérée ultérieure. Relais et connecteur restent désactivés.

Risques : bref rechargement partagé ; DDL MySQL non transactionnel (migrations additives) ; nouveau stockage à sauvegarder ; intégration OAuth encore à décider. La première publication ne signifie pas que cette conversation peut déjà synchroniser ses missions.
