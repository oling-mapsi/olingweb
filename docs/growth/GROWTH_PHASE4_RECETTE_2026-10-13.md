# Growth Phase 4 - Recette locale OLING Admin

Date : 2026-10-13

## Etat de connexion Growth Factory

- OLING local : `http://127.0.0.1:8061`
- Growth Factory isolé : `http://127.0.0.1:8070`
- Base OLING de test : SQLite `/private/tmp/oling_growth_phase4.db`
- Base Growth Factory de test : SQLite `/private/tmp/mapsi_growth_phase4_isolated.db`
- `GROWTH_FACTORY_ENABLED=1` uniquement sur cette recette locale.
- JWT serveur-a-serveur vérifié en RS256 avec `kid`, `issuer`, `audience`, expiration courte et rôles Growth.
- Aucun secret de production utilisé ou affiché.
- Publications testées uniquement avec publishers mock/simulés.

## Tests exécutés

| Périmètre | Commande | Résultat |
| --- | --- | --- |
| Contrat Growth Factory `/studio-simple/*` | `python3 -m pytest tests/integration/test_simple_campaign_api.py -q` | OK, 7 tests |
| Client OLING Growth Factory | `./vendor/bin/phpunit --do-not-cache-result tests/GrowthFactoryClientTest.php` | OK, 5 tests, 14 assertions |
| Vue OLING Growth | `./vendor/bin/phpunit --do-not-cache-result tests/GrowthFactoryViewTest.php` | OK, 2 tests, 6 assertions |
| Non-régression Analytics | `./vendor/bin/phpunit --do-not-cache-result tests/AnalyticsTrackingTest.php` | OK, 7 tests, 26 assertions |
| Non-régression agent IA commercial | `./vendor/bin/phpunit --do-not-cache-result tests/ChatConversationManagerTest.php` | OK, 3 tests, 6 assertions |
| Non-régression contact/admin | `./vendor/bin/phpunit --do-not-cache-result tests/ContactControllerTest.php` | OK, 5 tests, 17 assertions |
| Routes Growth OLING | `php bin/console debug:router --env=dev \| rg admin_growth` | OK, routes index/create/show/generate/update/preview/publish/delete |
| Recette navigateur | `node tests/e2e/growth-phase4-recette.mjs` | OK |
| Etat API indisponible | Script Playwright local | OK |

## Parcours validé

- Connexion administrateur locale.
- Accès `/admin/growth` depuis le menu admin.
- Création et génération pour `OLING`, `MAPSI_USERS`, `MAPSI_MARKETING`.
- Canaux vérifiés : `mapsi_site`, `oling_site`, `linkedin_manual`, `email_manual`.
- Edition séparée par canal.
- Versionnement par `expected_version`.
- Conflit obsolète vérifié en `409 Conflict`.
- Prévisualisation HTML sandboxée par canal.
- Publication simulée sans effet externe.
- Suppression d'une campagne non publiée.
- Archivage/suppression d'une campagne publiée fictive via le comportement Growth Factory existant.
- Etat d'indisponibilité affiché si Growth Factory ne répond pas.

## Captures

- `docs/growth/screenshots/growth-index-desktop.png`
- `docs/growth/screenshots/growth-index-mobile.png`
- `docs/growth/screenshots/growth-oling-show.png`
- `docs/growth/screenshots/growth-preview.png`
- `docs/growth/screenshots/growth-api-unavailable.png`

## Anomalies corrigées

- Ajout du rendu de prévisualisation par asset dans OLING Admin.
- Gestion explicite OLING des erreurs Growth `422` et `429`.
- Script Playwright stabilisé avec thèmes uniques et sélection explicite des canaux compatibles.
- Growth Factory : incompatibilité constatée sur `expected_version`, corrigée localement pour mapper `OptimisticLockError` en `409 Conflict`.

## Points non finalisés

- Le workflow simple Growth Factory auto-approuve encore les assets après génération ; il manque un vrai endpoint d'approbation séparé.
- Il n'existe pas encore d'endpoint `/studio-simple/*` dédié à l'archivage distinct de la suppression.
- Weekly Packs, Source Packs, retry, schedule et channel settings restent présents côté Growth Factory mais non exposés dans le MVP OLING Admin.
- L'environnement local a nécessité une substitution de dépendance pour utiliser le générateur éditorial simulé, car le backend simple force aujourd'hui le générateur réel.
- Les migrations Growth Factory Alembic ne passent pas sur SQLite isolé à cause d'une migration `ALTER COLUMN`; la recette a donc utilisé `Base.metadata.create_all`.

## Sécurité

- Accès protégé par les droits admin OLING.
- Actions POST protégées par CSRF.
- JWT émis côté serveur uniquement, non exposé au navigateur.
- Preview rendue dans une iframe sandbox.
- Aucune publication réelle vers `mapsi.fr`, `oling.fr`, LinkedIn ou email.

## GO / NO-GO

Décision : **NO-GO production**.

Le cockpit OLING est fonctionnel en local avec Growth Factory isolé, mais la production doit attendre :

- endpoint d'approbation explicite,
- endpoint d'archivage clair,
- environnement Growth Factory de recette pérenne avec migrations compatibles,
- mode générateur simulé configurable sans override local,
- validation finale XSS/sanitation sur contenus réels.

## Plan de déploiement et rollback

1. Préparer un environnement Growth Factory staging avec base synthétique et clés JWT dédiées.
2. Activer `GROWTH_FACTORY_ENABLED=1` uniquement en staging.
3. Rejouer cette recette avec les endpoints d'approbation/archivage finalisés.
4. Déployer OLING avec feature flag désactivé.
5. Activer le flag pour comptes administrateurs pilotes.
6. Rollback : désactiver immédiatement `GROWTH_FACTORY_ENABLED`, puis revenir au dernier tag OLING si nécessaire. Aucune donnée Growth n'est dupliquée dans OLING.
