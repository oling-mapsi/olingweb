# CRM OLING

Module Symfony privé : `/admin/crm`. Authentification et rôle `ROLE_ADMIN` existants ; toutes les mutations UI sont POST + CSRF. Données commerciales uniquement en production.

## Échéances et propositions à relancer

Trois indicateurs calculés sur tout le portefeuille, indépendamment des filtres : échéances à remettre sous 7 jours (retards inclus et signalés), au-delà de 7 jours, propositions envoyées ou en discussion en attente de réponse. Les marchés et demandes clients à suivre/en préparation sont inclus ; les statuts commerciaux priment sur les anciennes décisions de veille. Une sélection de veille explicite reste éligible au stade À qualifier. Les fiches Sans suite, gagnées, perdues, archivées, fusionnées ou issues de l’ancien import email sont exclues. La borne J+7 utilise Europe/Paris, changement d’heure compris. Les dates manquantes ne sont pas comptées et sont signalées. Les listes complètes sont accessibles au survol, au clic et au clavier, avec liens vers les fiches. Les propositions en attente ne nécessitent pas d’échéance ; leur date de relance sert au tri. Le lien Imports à réviser est retiré après nettoyage de l’historique.

## Interface V2.1 (branche locale, non déployée)

Cinq colonnes actives par défaut, filtre En cours / Clôturées / Toutes les étapes, détails des cartes au clic, textes et contrastes renforcés. Actions et relances s’ouvre en liste : relance et dépôt sont séparés, triés par la première date utile. Une date de dépôt d’une offre déjà envoyée n’est pas présentée comme une relance en retard. Aucun calcul de KPI ni donnée métier n’est modifié.

Voir [la recette et les captures V2.1](UX-V2.1.md). Branche locale `feat/crm-ux-v2-1`, sans fusion ni déploiement autorisé.

## Navigation et avancement

Une seule entrée **Portefeuille**, avec les filtres **Tous / Marchés publics / Clients**, et une entrée **Actions et relances**. Tous les Kanbans et listes affichent les statuts d'avancement enregistrés, y compris pour les marchés de veille et les marchés saisis manuellement. Les anciennes URL `view=markets` et `view=clients` ouvrent le portefeuille filtré correspondant. Les décisions de veille restent accessibles dans la fiche, sans remplacer les colonnes d'avancement ni masquer une affaire déjà en cours dans les actions. Aucun statut existant n'est migré ou réécrit.

## Politique clients en vigueur depuis le 23 octobre 2026

Alimentation automatique limitée aux formulaires OLING.fr et MAPSI.fr. Les saisies manuelles restent disponibles. Microsoft 365 est désactivé (`email_enabled: false`, `client_intake: forms_only`). La politique `forms_only` est aussi le défaut du code : un ancien indicateur email actif ne suffit pas à relancer la lecture. Toute réactivation exige une nouvelle décision métier et le mode explicite `email_and_forms`. Les instructions Microsoft ci-dessous décrivent la capacité historique, actuellement inactive.

Voir [nettoyage et contrôle des reprises](CLIENT-INTAKE.md).

Les coordonnées envoyées via le formulaire intégré au chat OLING sont aussi des demandes clients : origine `form`, formulaire `chat_contact`, statut initial `qualify`. Le raccordement traite uniquement un lead enregistré avec consentement, jamais une simple conversation anonyme. La référence stable `OLING|chat-lead-<id>` empêche les doublons lors d’un rejeu et respecte le blocage après suppression. Aucune lecture d’email, aucun appel à un modèle IA ni notification supplémentaire.

Le subscriber livre immédiatement après la réponse réussie du formulaire. La tâche existante reprend les reçus même après une panne de file ou de CRM. La reprise historique du chat est bornée par `crm_state.chat_forms.cursor`, initialisé explicitement au dernier identifiant à exclure lors de l’activation ; sans ce curseur, pas de reprise historique implicite. Pour la mise en service du 23/10/2026 : exclure le lead 1 ancien, reprendre à partir du lead 2 (envoi de contrôle). Activation après bascule : `APP_ENV=prod APP_DEBUG=0 php scripts/crm/enable-chat-recovery.php 2`, puis exécution normale de `app:crm:sync --scheduled`. Ne pas copier ces données en développement.

## Suppression et blocage des réimports

Depuis une fiche : **Supprimer et bloquer les réimports**, puis confirmation dédiée. Suppression transactionnelle de la fiche, notes, sources, historique et fiches fusionnées ; une fiche modifiée entre-temps exige de recharger. Accès administrateur, POST et CSRF obligatoires. Aucun email ni document externe n’est supprimé.

Seules les empreintes SHA256 des sources et identités connues restent dans `crm_state` (date et identifiant interne, aucun contenu commercial). Les imports suivants sont ignorés, y compris un enrichissement avec un autre identifiant agent si acheteur/référence/lot correspondent. Les autres lots restent importables. La comparaison normalise casse et espaces ; aucune déduction par titre seul. Un avis sans aucun identifiant ou référence en commun ne peut pas être reconnu comme le même marché.

Le connecteur répond HTTP 200 avec `deleted: true`, `ignored: true`, `reason: deleted_by_user`, sans lien vers une fiche inexistante. L’agent doit considérer cette réponse comme définitive. Les empreintes sont sauvegardées avec la base et doivent être conservées lors des déploiements/retours arrière. Aucune migration nouvelle nécessaire.

## Développement

Dépôt de travail isolé : `/Users/florestanrouet/myweb/olingw-crm`, branche UX locale `feat/crm-ux-v2-1`.

```sh
composer install --no-scripts
scripts/crm/local-start.sh
```

Adresse : http://127.0.0.1:8092/admin/crm. Le premier lancement crée une base SQLite synthétique et un compte `crm-demo` ; mot de passe aléatoire dans `var/crm-local-login.txt` (0600, ignoré). `.env.local`, base et identifiants ne sont jamais livrés. Config de lancement : environnement dev, mailer null, connecteurs désactivés. Ne jamais copier une base réelle dans cette base locale.

Le `vendor` historique est partiellement versionné : pour une installation neuve, reconstruire complètement les dépendances depuis `composer.lock`, ne pas se fier au répertoire présent dans Git. Le déploiement CRM réutilise les dépendances homologuées de la release serveur (aucune nouvelle dépendance).

## Fonctionnement

- Portefeuille unique filtrable (Tous, Marchés publics, Clients) et vue Actions et relances ; recherche, Kanban/liste, pagination et CSV. Les offres envoyées restent visibles après échéance.
- Fiche et historique : notes, montants estimés/proposés, relances, dates Paris affichées, stockage UTC, source et extrait. Champs inconnus laissés vides.
- Déduplication : clés SHA256 uniques en base, Message-ID par boîte pour emails, identité de conversation ; veille par identifiant stable avec alias acheteur/référence/lot. Un enrichissement ultérieur de la référence conserve la même fiche. Rejouer une page ou un import ne duplique pas les sources.
- Une adresse email commune suggère un rapprochement ; aucune fusion automatique par seul contact. Fusion explicite conserve la fiche originale dans l’historique et réaffecte les échanges futurs.
- Les corrections manuelles, notes et actions sont préservées. Les champs contradictoires et statuts détectés sont proposés avec leur source et validables un par un. Verrouillage optimiste contre les modifications simultanées.
- Veille explicitement « à suivre » : nouvelle fiche suivie, ou passage de qualification à suivi si aucun statut manuel ne s’y oppose. Autres résultats restent à qualifier.

## Microsoft 365

Configuration privée `/etc/oling/crm.json`, root:www-data 0640, dossier 0750. Chargée uniquement en `prod`. Jeton OAuth dans `/var/lib/oling-crm/oauth.json`, www-data 0600. Aucun secret dans Git, HTML, logs ou exports.

Connexion déléguée `Mail.Read` + `offline_access`, compte professionnel désigné. Renouvellement sur serveur indépendant de ChatGPT ; les politiques Microsoft peuvent imposer une nouvelle connexion après révocation/expiration. Une application Entra publique doit autoriser le code d’appareil. Connexion initiale :

```sh
sudo -u www-data python3 /var/www/oling-current/scripts/crm/connect-microsoft.py
```

Le script affiche un code à confirmer chez Microsoft et enregistre les jetons exclusivement sur le serveur. Alternative prise en charge : application confidentielle avec `client_secret` et permission applicative Mail.Read, à limiter à la boîte via les contrôles Exchange avant activation.

Le client ne fait que GET sur Graph, aucun déplacement, changement de lecture, suppression ou envoi. ID immuables, pagination bornée, curseur persistant après chaque page, reprise sur erreur, verrou interprocessus. Historique par défaut douze mois, `app:crm:sync --since=AAAA-MM-JJ` pour une reprise explicite. Deux passes initiales rattachent aussi les échanges non commerciaux lus avant la demande détectée. Cadence horaire ; timer vérifie toutes les cinq minutes les relances manuelles et reprises de pagination. Les erreurs HTTP sont affichées sans réponse OAuth brute.

Les notifications structurées du formulaire OLING sont reconnues avec origine `form`, ainsi que les confirmations automatiques explicites de dépôt. Extraction isolée dans `EmailExtractor`, règles déterministes remplaçables. Ollama non actif sur l’hôte lors de l’examen. Aucun contenu d’email n’est transmis à un moteur IA externe. Cette extraction n’assure pas une reconnaissance exhaustive : phrases ambiguës, pièces jointes et projets répartis sur plusieurs conversations nécessitent une qualification/validation. Les emails sont toujours traités comme données, jamais comme instructions exécutables.

Documentation Microsoft : [lecture des messages](https://learn.microsoft.com/en-us/graph/api/user-list-messages?view=graph-rest-1.0), [identifiants immuables](https://learn.microsoft.com/en-us/graph/outlook-immutable-id).

## Veille marchés

Transport agent : `https://oling.fr/api/crm/mcp`, Streamable HTTP stateless, authentification Bearer. Deux outils : `crm_connection_status` (aucune donnée commerciale) et `crm_import_tender` (création/enrichissement uniquement). Aucun accès aux emails ou lecture globale du CRM n’est exposé. `dry_run: true` teste la chaîne d’import en annulant la transaction ; seule la preuve de connexion est conservée. Dans ChatGPT, application personnalisée avec « Jeton d’accès / clé API », schéma « Porteur ». Le secret est conservé par le gestionnaire d’identifiants du connecteur, jamais dans les instructions de l’agent.

L’agent doit utiliser un identifiant constant par avis/lot, `follow: false` pour les suggestions, `follow: true` seulement après décision explicite de l’utilisateur. Les statuts manuels préexistants restent protégés. Tester connexion, import, enrichissement et rejeu depuis l’agent avant de le déclarer connecté.


`POST https://oling.fr/api/crm/watch`, `Authorization: Bearer <secret>`, JSON, taille maximale 64 Kio. Jeton serveur aléatoire d’au moins 32 caractères ; activer `watch_enabled` seulement après raccordement de l’agent. Ne jamais intégrer ce secret dans un prompt, du JavaScript de navigateur ou une URL.

```json
{"id":"avis-exemple-lot-1","title":"Exemple fictif : AMOA","follow":true,"url":"https://example.org/avis/1","fields":{"buyer":"Acheteur Exemple","reference":"EX-01","lot":"1","deadline":"2026-12-01T12:00:00+01:00"}}
```

Réponses : 201 nouvel événement, 200 rejoué, 403 non autorisé/désactivé, 409 concurrence (réessayer), 413 taille, 422 schéma. Le raccordement réel de l’agent doit être constaté séparément de l’existence de l’endpoint. Ne jamais présenter un import de test comme une connexion réelle.

## Tests

```sh
SYMFONY_DEPRECATIONS_HELPER=disabled php bin/phpunit --filter 'App\\Tests\\Crm'
php bin/console lint:container
php bin/console lint:twig templates/admin/crm
```

Tests synthétiques : accès anonyme et non-admin, CSRF, création/suivi/export, conservation après échéance, sources rejouées, corrections manuelles, séparation de projets, fusion validée, concurrence, liens dangereux et désactivation locale. La recette de production doit vérifier également les dépendances et templates exacts de la release candidate.

## Déploiement et rollback

Suivre le mécanisme du dépôt : verrou `/var/lock/oling-deploy-cms-p0.lock`, copie de la release active, overlay CRM explicite, conservation des secrets/médias liés, migrations additives, cache comme www-data, bascule atomique `/var/www/oling-current`, reload Apache seulement. Le workspace ne doit jamais être déployé en bloc.

`scripts/crm/deploy.py` prépare sauvegarde/release et bascule avec contrôles. Migration ciblée `DoctrineMigrations\Version20261022220000` (cinq nouvelles tables), jamais les autres migrations pendantes. Ne pas utiliser `doctrine:schema:update --force` en production : les tables CRM sont gérées par la migration DBAL.

Sauvegarde chiffrée base/code/médias/configurations, copie hors hôte et vérification avant bascule. Conserver la release précédente. En cas d’échec : arrêter le timer CRM, restaurer le pointeur précédent et recharger Apache. Garder les tables et données CRM : aucune migration down ni ancienne base restaurée automatiquement. La migration down refuse une base CRM non vide.
