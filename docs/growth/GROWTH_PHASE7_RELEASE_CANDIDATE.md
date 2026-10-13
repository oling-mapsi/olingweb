# Growth Phase 7 - Release candidate OLING

Date : 2026-10-13

## Decision

**GO release candidate technique locale. NO-GO production directe.**

Aucun deploiement, push, merge, publication externe, email reel ou retrait de MAPSI Studio n'a ete effectue.

## 1. Etat Git final

OLING Web :

- Branche : `codex-growth-oling-admin-integration`
- Base constatee : `origin/main` = `e8336261`
- Commits Growth applicatifs : `94acaf2a`, `6be3c08c`, `b548efce`, `4c9fee1b`
- Commit Phase 7 : `Prepare Growth release candidate` ; SHA final a verifier avec `git log -1 --oneline` et reporte dans le compte rendu Codex.

Growth Factory :

- Branche : `codex-studio-simple-growth-final`
- Base constatee : `origin/main` = `460beb8`
- Commit Phase 7 : `13f4533 Run simple Growth integration tests on PostgreSQL`
- Modifications locales non liees conservees non stagees.

## 2. Architecture staging obtenue

- OLING Symfony local : `http://127.0.0.1:8061`
- Growth Factory FastAPI local : `http://127.0.0.1:8070`
- PostgreSQL local dedie : `mapsi_growth_phase7_staging`
- OLING DB locale : `/private/tmp/oling_growth_phase7.db`
- JWT RS256 de recette : fichiers `/private/tmp/oling_growth_factory_jwt_phase7.*`, hors Git
- IA : `GROWTH_GENERATOR_MODE=simulated`
- Publishers : `OLING_MODE=mock`, `MAPSI_SITE_MODE=mock`, canaux LinkedIn/email manuels
- Admin pilote : `phase7@example.test`

Scripts ajoutes :

- `bin/growth-phase7-env`
- `bin/growth-phase7-reset`
- `bin/growth-phase7-health`
- `bin/growth-phase7-stop`

Le reset exige `GROWTH_PHASE7_CONFIRM_RESET=RESET_PHASE7`, force `localhost/127.0.0.1/::1`, et refuse les bases sans marqueur `phase7`, `staging` ou `test`.

## 3. Tests PostgreSQL reels

Correction apportee dans Growth Factory :

- `tests/conftest.py` utilise `GROWTH_TEST_DATABASE_URL` si fourni ;
- garde anti-erreur : PostgreSQL autorise uniquement en local avec nom de base de test/staging ;
- test explicite du dialecte et du nom de base ajoute.

Commandes :

```bash
GROWTH_PHASE7_CONFIRM_RESET=RESET_PHASE7 bash bin/growth-phase7-reset
source bin/growth-phase7-env && python3 -m pytest tests/integration/test_simple_campaign_api.py tests/unit/test_simple_campaign_service.py tests/unit/test_html_sanitizer.py -q
```

Resultat : **OK, 14 tests**, dialecte `postgresql` verifie.

Valide : migrations depuis base vierge, second `alembic upgrade head`, creation campagnes/assets, `expected_version`, conflits `409`, revue, approbation, publication simulee, depublication ciblee, suppression logique, archivage, sanitation HTML, erreurs `401/403/404/422`.

Note : les tests fonctionnels recreent le schema par fixture ; apres leur execution, le reset staging doit etre rejoue avant lancement serveur.

## 4. Contrat API

Endpoints `/studio-simple/*` valides :

- `GET /campaigns`
- `POST /campaigns`
- `GET /campaigns/{id}`
- `POST /campaigns/{id}/generate`
- `PUT /campaigns/{id}/assets/{asset_id}`
- `POST /campaigns/{id}/assets/{asset_id}/review`
- `POST /campaigns/{id}/assets/{asset_id}/approve`
- `POST /campaigns/{id}/publish`
- `POST /campaigns/{id}/unpublish`
- `POST /campaigns/{id}/archive`
- `DELETE /campaigns/{id}`
- `GET /oling-themes`

Publication partielle et depublication ciblee : OK.

## 5. Securite

- JWT RS256 valide avec `issuer`, `audience`, `kid`, TTL et permissions.
- Replay protection active cote Growth Factory.
- CSRF present sur actions OLING sensibles.
- `GROWTH_FACTORY_ENABLED=0` par defaut hors env explicite.
- Restriction pilote ajoutee : `GROWTH_FACTORY_ALLOWED_ADMIN_EMAILS`.
- Sanitation HTML centralisee cote Growth Factory.
- Preview OLING en iframe sandbox conservee.
- Secrets de recette hors Git.

## 6. Publishers

Valide sans effet public :

- `oling_site` mock ;
- `mapsi_site` mock ;
- `linkedin_manual` manuel ;
- `email_manual` manuel.

Publication reelle OLING/MAPSI, LinkedIn et email : **NON VERIFIEE** volontairement, faute de sandbox fournisseur prouvee et par interdiction de publication publique.

## 7. Inventaire historique

Aucun acces autorise en lecture seule au referentiel historique Growth Factory de production n'a ete utilise.  
Cette verification reste **bloquante avant decommission de MAPSI Studio**.

## 8. Recette Playwright

Commande :

```bash
OLING_BASE_URL=http://127.0.0.1:8061 OLING_ADMIN_USERNAME=phase7 OLING_ADMIN_PASSWORD=testpass node tests/e2e/growth-phase7-recette.mjs
```

Resultat final : **OK**.

Parcours couvert : login admin, navigation admin Growth, `OLING`, `MAPSI_USERS`, `MAPSI_MARKETING`, quatre canaux, generation simulee, edition, conflit de version, preview, refus publication sans approbation, revue, approbation, publication mock, suppression campagne non publiee, archivage campagne publiee fictive.

Captures mises a jour :

- `docs/growth/screenshots/growth-index-desktop.png`
- `docs/growth/screenshots/growth-index-mobile.png`
- `docs/growth/screenshots/growth-oling-show.png`
- `docs/growth/screenshots/growth-preview.png`

## 9. Non-regression OLING

Valide :

- `php -l src/Controller/GrowthAdminController.php`
- `./vendor/bin/phpunit --do-not-cache-result tests/GrowthFactoryClientTest.php` : OK, 5 tests
- `./vendor/bin/phpunit --do-not-cache-result tests/GrowthFactoryViewTest.php` : OK, 2 tests
- `./vendor/bin/phpunit --do-not-cache-result tests/AnalyticsTrackingTest.php` : OK, 7 tests
- `./vendor/bin/phpunit --do-not-cache-result tests/ChatConversationManagerTest.php` : OK, 3 tests
- `./vendor/bin/phpunit --do-not-cache-result tests/ContactControllerTest.php` : OK, 5 tests

## 10. Fonctions avancees encore MAPSI Studio

- Weekly Packs
- Source Packs
- retry avance
- schedule/planification avancee
- channel settings
- inventaire historique complet
- ecrans d'administration fine des publishers

Ces capacites restent dans Growth Factory ; OLING ne les expose pas encore toutes.

## 11. Runbook futur

1. Sauvegarder OLING et Growth Factory.
2. Installer secrets hors Git : JWT private/public keys, tokens sandbox/live, OpenAI si active.
3. Deployer Growth Factory candidate.
4. Executer Alembic `upgrade head`.
5. Verifier `/healthz`.
6. Deployer OLING candidate avec `GROWTH_FACTORY_ENABLED=0`.
7. Configurer `GROWTH_FACTORY_ALLOWED_ADMIN_EMAILS` pour pilotes.
8. Activer `GROWTH_FACTORY_ENABLED=1` uniquement pour recette pilote.
9. Smoke-test : liste, creation, generation, preview, revue, approbation, publication sandbox, depublication.
10. Sur anomalie : remettre `GROWTH_FACTORY_ENABLED=0`, conserver MAPSI Studio, stopper workers Growth si necessaire, diagnostiquer sans supprimer de campagnes.

## 12. Risques residuels

- Pas de staging reseau public/partage certifie.
- Pas d'inventaire historique autorise.
- Publishers reels non certifies.
- OpenAI reel non rejoue.
- MAPSI Studio doit rester disponible.

## 13. Decision production

**NO-GO production directe.**

Pre-requis avant GO production :

- staging reseau isole et autorise ;
- inventaire historique read-only ;
- recette publishers en sandbox fiable ou preview-only ;
- validation metier ;
- revue securite finale ;
- plan rollback execute a blanc.
