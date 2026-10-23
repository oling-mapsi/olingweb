# CRM 2 — canaux et espace commercial

Branche `fix/crm-channels-and-workspace`. Les données existantes restent dans les mêmes tables et sous la même authentification.

## Parcours

- **Portefeuille** : affaires privées sorties de qualification et marchés sélectionnés « À suivre ». Le choix d’un marché est distinct du statut commercial.
- **Marchés publics** : une fiche par avis/lot, avec les décisions À examiner / À suivre / Ne pas suivre. Le refus est conservé même si l’agent propose ensuite `follow=true`.
- **Demandes clients** : formulaires et demandes email plausibles. Les anciens imports email sans intervention manuelle sont isolés dans **Imports à réviser**, sans suppression ni fusion.
- **Actions et relances** : portefeuille avec une action, relance ou date limite. Recherche société/contact/référence, filtres origine/statut/échéance, liste et Kanban. Les déplacements et le sélecteur accessible appellent la même écriture protégée par version et CSRF.

Le CRM possède sa propre feuille de style et son JavaScript (`public/crm`). Il reste accessible depuis le back-office. Les erreurs de sauvegarde sont affichées ; un conflit n’écrase pas une autre modification.

## Formulaires

Le contrôleur OLING de production possède déjà une référence de soumission et des protections renforcées. `ContactReceiptSubscriber` intervient après sa réponse validée et persistée, sans remplacer ce contrôleur. La référence `OLING|submission_id` est commune à la réception directe et à la notification email. La table `messagerie` reste une source de reprise si le CRM est momentanément indisponible. Les attributs de provenance sont capturés dans la file privée.

MAPSI possède son propre dépôt `oling-mapsi/mapsi_web`, branche `fix/crm-form-delivery`. Son contrôleur conserve les contrôles CSRF et antispam, puis enregistre une réception privée durable avant transmission et notification. Une référence stable par session/contenu pendant 24 heures empêche les doublons lors des renvois. L’email technique contient la même référence et le même contenu structuré ; le contact vient des champs saisis, jamais de l’expéditeur technique. Les formulaires existants demandent parfois un nom complet : il est conservé tel quel, sans inventer la séparation prénom/nom.

Stockage production : `/var/lib/oling-crm/forms` (livraisons CRM en attente), `/var/lib/oling-crm/mapsi-receipts` (réceptions et état des notifications). Dossiers 0700 et fichiers 0600, utilisateur www-data. Configuration dans `/etc/oling/crm.json`, avec `forms_enabled`, `forms_token` distinct de la veille, `forms_dir`. Aucun secret dans le dépôt.

`app:crm:retry` côté MAPSI reprend livraison et notifications échouées. Une notification restée `sending` nécessite une vérification humaine, pour éviter un second envoi après une acceptation SMTP incertaine. `app:crm:sync` reprend la file et les réceptions OLING. Ces commandes utilisent le timer CRM existant ; aucun second planning n’est créé. L’état des reprises et des notifications MAPSI à vérifier apparaît dans Connexions et alimentation. Les alertes techniques n’exposent ni secret ni contenu des demandes.

Les connexions et les files de production ne sont jamais lues en dev/test. Lancement local : `scripts/crm/local-start.sh`, base SQLite fictive, mailer null, http://127.0.0.1:8092/admin/crm.

## Emails et veille

Outlook reste strictement Mail.Read. La présence isolée de mots commerciaux ne crée plus d’affaire. Les demandes candidates exigent une intention explicite et une demande adressée ; les propositions envoyées exigent une formulation de pièce jointe. Elles restent à qualifier. Les newsletters, listes et concentrateurs sont exclus avant tout rapprochement. Les réponses d’une conversation connue sont rattachées ; une adresse commune seule n’est qu’une suggestion de fusion.

Notifications reconnues : expéditeur exact `florestan.rouet@oling.fr`, sujet exact OLING ou sujet formulaire MAPSI connu, format structuré et référence partagée. Les anciens messages sans référence ne sont pas artificiellement fusionnés. Le besoin initial reste conservé ; les divergences ultérieures sont proposées dans l’historique.

MCP 2 expose `crm_tender_decisions` (pagination 100, uniquement avis/lot/décision/statut et liens, sans contacts ni emails), `crm_import_tender` et `crm_connection_status`. L’agent doit lire toutes les décisions avant ses imports, réutiliser l’identifiant d’avis et le lot, ne jamais importer un digest, conserver les informations inconnues vides et enrichir les fiches existantes. Son planning quotidien existant est conservé.

## Livraison et retour arrière

Déployer exclusivement l’overlay CRM avec `scripts/crm/deploy.py`. La liste autorisée inclut les nouveaux contrôleurs, services, templates et `public/crm/*`. La seule migration nouvelle est `DoctrineMigrations\Version20261022233000`, qui élargit les champs JSON pour préserver les demandes complètes. Aucune migration étrangère n’est exécutée. `reviewLegacy()` est idempotent et ajoute uniquement un indicateur et un événement de révision ; il conserve les fiches ayant un statut suivi ou une modification manuelle.

MAPSI : sauvegarder les seuls fichiers remplacés et les états/configurations serveur, vérifier leurs empreintes, puis déployer les cinq fichiers de sa branche et régénérer son cache. Ne pas synchroniser le workspace entier (il contient des travaux sans rapport).

Avant activation : sauvegarde chiffrée de base/code/configurations, test de restauration SQL, copie **archive chiffrée uniquement** dans `/Users/florestanrouet/myweb/oling-crm-backups/<commit>`. La clé reste sur le serveur. Conserver les sources privées des formulaires sur le serveur, jamais en développement.

Retour arrière : arrêter le timer et le service, désactiver `forms_enabled`, restaurer les fichiers MAPSI sauvegardés et son cache, rebascule du symlink OLING vers la release précédente puis reload Apache. Conserver tables, champs longs, réceptions et événements : aucune restauration ancienne de base n’est automatique. Les réceptions en attente sont reprises après correction.

## Vérifications

`SYMFONY_DEPRECATIONS_HELPER=disabled DATABASE_URL=sqlite:///:memory: MAILER_DSN=null://null APP_SECRET=crm-test-local php bin/phpunit --filter 'App\Tests\Crm'`

Tests : 17 scénarios, 114 assertions avant recette finale : avis/rejeu/enrichissement, refus persistant, corrections manuelles, formulaire direct + email, contact réel, exclusion digest, conversations distinctes/réponses rattachées, isolation locale, authentification/CSRF, mise à jour Kanban et conflit de version. `app:crm:check` teste également les dépendances et templates de production dans une transaction annulée.

Recette navigateur locale : déplacement réel de carte, rechargement confirmant le statut, alternative par sélecteur, fiche et note enregistrées à 390 × 844, présentation bureau. Captures fictives dans `var/crm-v2-desktop.jpg` et `var/crm-v2-mobile.jpg` (hors Git).

## Recette de production du 22 octobre 2026

Les PR OLING #14 et MAPSI #3 ont été intégrées. Le module OLING `f4432f6b68c3` et le raccordement MAPSI `b52e67af6f6c38aea6c3e3bd29daec854dca7c27` ont été déployés réellement. L'agent de veille a exécuté les deux outils : lecture des décisions réussie, import fictif `dry_run=true`, `persisted=false`. Son horaire quotidien de 08h Europe/Paris est conservé.

La recette serveur du formulaire MAPSI a validé la référence stable, le rejeu sans double notification (transport email simulé), le contrôleur authentifié OLING et la déduplication avec la notification structurée. Les écritures de recette CRM ont été annulées ; aucun email de test n'a été envoyé. Les anciens imports sont conservés pour révision.

Correctif de recette : exclusion explicite des alertes AW Solutions, 17 tests / 115 assertions. Le déploiement accepte les entrées de répertoire de `git archive` mais n'extrait que les fichiers autorisés. MAPSI utilise un autoloader optimisé : régénérer celui-ci avec son propriétaire `ubuntu`, avant le cache, y compris lors du retour arrière.

Paquet OLING reproductible (depuis le commit à livrer) :

```sh
git archive --format=tar.gz -o /tmp/oling-crm-release.tar.gz HEAD src/Service/Crm templates/admin/crm public/crm scripts/crm docs/crm src/Controller/CrmAdminController.php src/Controller/CrmImportController.php src/Controller/CrmFormController.php src/Command/CrmSyncCommand.php src/Command/CrmCheckCommand.php migrations/Version20261022220000.php migrations/Version20261022233000.php
```

Les empreintes et le commit effectifs se lisent dans `/var/www/oling-current/CRM_RELEASE.json`. Le manifeste de sauvegarde correspondant donne le chemin exact de retour arrière. Ne jamais copier la clé de sauvegarde hors serveur.

## Correction portefeuille / Kanban — 23 octobre 2026

Le portefeuille affiche désormais ensemble les demandes clients, y compris « À qualifier », et les marchés publics, quelle que soit leur décision. Seuls les imports explicitement placés en révision et les doublons fusionnés restent hors de cette vue. Le Kanban montre toutes les fiches correspondantes ; seule la liste est paginée. Trois boutons suffisent pour filtrer : Tous / Marchés publics / Clients ; le filtre marché inclut aussi les fiches publiques créées manuellement.

Les cartes se déplacent dans toutes les vues Kanban. Le portefeuille modifie le statut commercial ; la vue Marchés publics modifie la décision, sans changer le statut. Un statut commercial ne réinitialise jamais « Ne pas suivre ». Une poignée, la surbrillance de destination, le défilement horizontal aux bords et la confirmation d'enregistrement rendent le déplacement visible. Le sélecteur reste disponible au clavier et sur mobile. Les protections de version et CSRF sont conservées.

Recette : 17 tests CRM, 140 assertions, dont visibilité de la qualification et des marchés dans le portefeuille, filtres clients/publics, déplacement de décision, refus persistant et conflit concurrent. Aucune migration supplémentaire. Déploiement par le même overlay et retour arrière par la release précédente.
