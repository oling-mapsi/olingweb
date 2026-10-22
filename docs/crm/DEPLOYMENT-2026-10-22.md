# Livraison CRM — 22 octobre 2026

## Production et Git

- URL privée : https://oling.fr/admin/crm (authentification OLING, ROLE_ADMIN).
- Commit du module déployé : `129e5646fe2876f2523733a37659cc9aae54861b`.
- Release active : `/var/www/releases/oling-crm-129e5646fe28`, via `/var/www/oling-current`.
- Intégration : PR https://github.com/oling-mapsi/olingweb/pull/13, fusionnée dans `main` au commit `f7030852bdd4061284ff1ecc0a1c6684aab7bf04`.
- Déploiement par overlay de fichiers CRM explicitement vérifiés, sur la release existante. Les modifications de sécurité déjà présentes sur le serveur ont été conservées. Le commit ci-dessus identifie le module CRM, pas une réinstallation complète du dépôt historique.
- Manifestes SHA256 des fichiers : `CRM_RELEASE.json` dans la release et `manifest.json` dans la sauvegarde serveur.
- Migration `Version20261022220000` appliquée : cinq tables CRM additives. Aucune autre migration pendante exécutée.

## Sauvegarde et retour arrière

Sauvegarde chiffrée de la base, du code, des médias, des configurations et secrets serveur : `/var/backups/oling/oling-crm-129e5646fe28/snapshot.enc`. Restauration SQL de contrôle effectuée dans une base temporaire distincte, nombre de tables vérifié, base temporaire supprimée. Archive déchiffrée et table des fichiers vérifiée sur le serveur.

Copie hors serveur autorisée, chiffrée uniquement : `/Users/florestanrouet/myweb/oling-crm-backups/129e5646fe28/snapshot.enc`. SHA256 identique vérifié. Clé conservée exclusivement dans le dossier de sauvegarde serveur, permissions 0600 ; aucune clé dans la copie locale ni dans Git. Le dossier de sauvegarde local est séparé du worktree et de SQLite.

Retour à la première release CRM : `/var/www/releases/oling-crm-bb2345213753`. Retour avant CRM : `/var/www/releases/oling-security-20261022`.

Sous le verrou de déploiement habituel : arrêter `oling-crm-sync.timer` et `oling-crm-sync.service`, désactiver les trois indicateurs dans `/etc/oling/crm.json`, créer un nouveau symlink temporaire vers la release choisie, le déplacer atomiquement sur `/var/www/oling-current`, puis `systemctl reload apache2`. Contrôler `/`, `/contact`, `/login`. Conserver les tables CRM et leurs données ; aucune restauration d’une ancienne base ni migration down automatique.

## Vérifications effectuées

- PHPUnit local : **12 tests CRM, 81 assertions**, succès.
- Lint PHP, conteneur Symfony, quatre templates Twig et différences Git : succès.
- Deux contrôles GitHub : PHP/tests/build et aperçu Chromium isolé/SEO, succès.
- Recette sur les dépendances exactes de production : identification, création, déduplication, préservation manuelle et rendu Twig réussis dans une transaction annulée, aucune fiche fictive conservée.
- HTTP après bascule : `/`, `/contact`, `/login` = 200 ; `/admin/crm` anonyme = 302. Apache/MySQL actifs.
- HTTPS MCP : refus sans clé (401), négociation, liste des deux outils et import à blanc, succès.
- Navigation locale réelle : connexion, tableau, ouverture de fiche, note et prochaine action enregistrées sur données fictives. Automatismes affichés désactivés.
- **Vulcain ERP réel** : quatre échanges Microsoft lus sur serveur, dont la proposition du 14 octobre et la demande initiale. Deux conversations rapprochées après vérification du même contact et du projet ERP Vulcain dans les quatre sources. Une seule fiche active, quatre sources ; rejeu des quatre messages sans nouvelle source. Le statut détecté reste une proposition à valider.
- Fiche de recette métier réelle : https://oling.fr/admin/crm/ee601df948acf676c7ff0c18c7129e3c.

## Connexions et tâches

Microsoft 365 connecté durablement en lecture seule au compte désigné, via l’application Entra **OLING CRM Mail**, client `5aa23879-c095-47a9-a7ec-97bf8e2b2d16`. Consentement limité au compte de l’utilisateur ; aucun consentement global. Jeton renouvelable uniquement sur le serveur. Aucun envoi, déplacement, suppression ou changement de lecture effectué par le CRM.

Configuration : `/etc/oling/crm.json` ; OAuth : `/var/lib/oling-crm/oauth.json`. Timer `oling-crm-sync.timer` vérifie toutes les cinq minutes les reprises et relances, avec cadence de synchronisation normale d’une heure. Reprise initiale depuis le 22 octobre 2025, pagination et deuxième passe persistantes. Cette reprise reste en cours au relevé de livraison ; la synchronisation horaire normale prend le relais à son achèvement. Les nouvelles fiches sont déjà accessibles. Les lots et reprises utilisent le même verrou exclusif, y compris pendant la recette. Un lot a rencontré une erreur transitoire pendant la reprise ; la relance au curseur sauvegardé a repris le traitement et effacé l’erreur opérationnelle sans perte des fiches déjà importées.

Agent **Veille marchés publics OLING** raccordé à l’application privée **OLING CRM**, endpoint `https://oling.fr/api/crm/mcp`, authentification Bearer conservée dans le gestionnaire de connexion ChatGPT. Le fichier temporaire de transfert autorisé a été supprimé immédiatement après saisie. Application connectée dans l’agent et instructions mises à jour : suggestions à qualifier ; `follow=true` exclusivement sur décision explicite « à suivre » ; identifiant stable par avis/lot ; aucune donnée inconnue inventée.

Recette appelée **depuis l’agent** le 22 octobre à 20:21 UTC : `connected=true`, `dry_run=true`, `persisted=false`. La preuve serveur `watch_connection` confirme l’appel ; aucune fiche de recette retrouvée en base. Le test ne constitue pas un import métier fictif. Le planning de veille quotidien existant à 08 h est conservé ; aucun nouveau planning parallèle n’est créé. Après accord explicite de l’utilisateur, la sécurité des actions du seul connecteur OLING CRM est réglée sur « Autoriser les outils à faible risque ». Les imports ordinaires peuvent donc être approuvés automatiquement ; les opérations jugées sensibles restent soumises au contrôle ChatGPT. Une nouvelle recette à blanc exécutée sans approbation manuelle a effectivement atteint le serveur à 20:31:09 UTC : `created=true`, `dry_run=true`, `persisted=false`.

L’extraction email déterministe reste volontairement prudente : pas de lecture des pièces jointes ni d’inférence exhaustive de tous les champs. Les projets distincts chez un même contact ne sont pas fusionnés automatiquement. Les rapprochements incertains et changements de statut restent à valider dans le CRM. Ollama était inactif ; aucun email n’est envoyé à un moteur IA externe.

## Version locale

Worktree complet : `/Users/florestanrouet/myweb/olingw-crm`, branche dédiée `feature/oling-crm` intégrée dans `main`. Le clone initial `/Users/florestanrouet/myweb/olingw` et ses modifications en cours ont été préservés.

```sh
cd /Users/florestanrouet/myweb/olingw-crm
scripts/crm/local-start.sh
```

URL : http://127.0.0.1:8092/admin/crm. Compte fictif `crm-demo`, mot de passe aléatoire dans `var/crm-local-login.txt`. Base SQLite `var/crm-local.db`, configurations et identifiants ignorés par Git. Aucun usage métier en local. Synchronisations, veille, tâches et mailer désactivés par défaut et protégés par l’environnement `dev`.

Dépendances locales reconstruites depuis `composer.lock` (le dépôt historique versionne partiellement `vendor`). Les différences locales de fichiers d’autoload ne font pas partie des commits CRM et ne sont pas déployées ; la production réutilise ses dépendances homologuées.
