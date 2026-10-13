# Growth Phase 8 - Deployment readiness et pilote OLING

Date : 2026-10-13

## Decision courte

- Installation production inactive, `GROWTH_FACTORY_ENABLED=0` : **GO conditionnel** apres revue humaine des PR et sauvegardes.
- Activation pilote OLING : **NO-GO** tant qu'un staging reseau autorise n'a pas ete certifie.
- Publications reelles : **NO-GO**.
- Decommission MAPSI Studio : **NO-GO**.

Aucun deploy, push, merge, DNS, serveur, publication externe, email reel ou suppression MAPSI Studio n'a ete execute.

## 1. Branches et SHA

OLING Web :

- Branche de travail : `codex-growth-oling-admin-integration`
- Branche candidate locale : `codex/growth-oling-phase8-rc`
- SHA candidat : `d4a7365c`
- Base : `origin/main` = `e8336261`
- Ecart : `0` commit en retard, `5` commits Growth en avance.

Growth Factory :

- Branche de travail : `codex-studio-simple-growth-final`
- Branche candidate locale : `codex/growth-factory-phase8-rc`
- SHA candidat : `13f4533`
- Base : `origin/main` = `460beb8`
- Ecart : `0` commit en retard, `19` commits Growth en avance.
- Modifications locales non liees conservees non stagees.

PR a preparer apres push autorise :

- OLING : `codex/growth-oling-phase8-rc` -> `main`
- Growth Factory : `codex/growth-factory-phase8-rc` -> `main`

## 2. Tests executes

Growth Factory PostgreSQL reel :

```bash
source bin/growth-phase7-env
python3 -m pytest tests/integration/test_simple_campaign_api.py tests/unit/test_simple_campaign_service.py tests/unit/test_html_sanitizer.py -q
```

Resultat : **OK, 14 tests**, dialecte `postgresql` verifie par test.

OLING :

- `php -l src/Controller/GrowthAdminController.php` : OK
- `php -l src/Service/GrowthFactory/GrowthFactoryClient.php` : OK
- `php -l src/Service/GrowthFactory/GrowthFactoryView.php` : OK
- `tests/GrowthFactoryClientTest.php` : OK, 5 tests, 18 assertions
- `tests/GrowthFactoryViewTest.php` : OK, 2 tests, 6 assertions
- `tests/AnalyticsTrackingTest.php` : OK, 7 tests, 26 assertions
- `tests/ChatConversationManagerTest.php` : OK, 3 tests, 6 assertions
- `tests/ContactControllerTest.php` : OK, 5 tests, 17 assertions

Contrats/routes :

- Routes OLING `/admin/growth/*` verifiees avec `debug:router`.
- OpenAPI local Growth Factory verifie : endpoints `/studio-simple/*` presents.

## 3. Etat reel de `growth.oling.fr`

Sondes read-only executees :

```bash
curl -fsSIL --max-time 10 https://growth.oling.fr/healthz
curl -fsSIL --max-time 10 https://growth.oling.fr/openapi.json
curl -fsSIL --max-time 10 https://growth.oling.fr/studio-simple/campaigns
curl -fsSIL --max-time 10 http://growth.oling.fr/healthz
```

Resultat : connexion impossible depuis cet environnement sur ports `443` et `80`.

Conclusion : l'etat applicatif, PostgreSQL, Redis/workers, version deployee, JWT et publishers de `growth.oling.fr` ne sont **pas certifies** en Phase 8.

## 4. Staging reseau

Aucun staging reseau autorise et joignable n'a ete identifie.  
La recette Phase 8 ne remplace donc pas ce controle par une simple recette locale supplementaire.

Ressources necessaires avant activation pilote :

- URL staging OLING accessible aux testeurs.
- URL staging Growth Factory accessible depuis OLING staging.
- PostgreSQL staging dedie.
- Redis/worker staging si requis.
- Cles JWT RS256 staging.
- `GROWTH_GENERATOR_MODE=simulated`.
- Publishers mock/preview-only prouves isoles.
- Admins pilotes limites par `GROWTH_FACTORY_ALLOWED_ADMIN_EMAILS`.

## 5. Certificats de recette

Derniere recette navigateur complete : Phase 7, Playwright local, OLING -> Growth Factory -> PostgreSQL local.

Commande :

```bash
OLING_BASE_URL=http://127.0.0.1:8061 OLING_ADMIN_USERNAME=phase7 OLING_ADMIN_PASSWORD=testpass node tests/e2e/growth-phase7-recette.mjs
```

Resultat : OK.

Limite : non rejouee sur staging reseau, donc insuffisante pour GO pilote.

## 6. Inventaire historique

Aucun acces read-only autorise au referentiel Growth Factory historique n'a ete disponible.  
Inventaire campagnes/assets/statuts/publications/historiques : **NON VERIFIE**.

Bloquant avant :

- activation pilote sur donnees reelles ;
- bascule complete depuis MAPSI Studio ;
- decommission MAPSI Studio.

## 7. Connecteurs reels

| Connecteur | Etat Phase 8 |
| --- | --- |
| `oling_site` | Mock/local certifie ; reel NON VERIFIE |
| `mapsi_site` | Mock/local certifie ; reel NON VERIFIE |
| `linkedin_manual` | Manuel, aucune publication API |
| `email_manual` | Manuel, aucun envoi email |

Les publications reelles restent interdites tant que sandbox/preview-only et contrats live ne sont pas verifies.

## 8. Configuration et secrets

OLING requis :

- `GROWTH_FACTORY_ENABLED=0` par defaut.
- `GROWTH_FACTORY_BASE_URL`.
- `GROWTH_FACTORY_JWT_ISSUER`.
- `GROWTH_FACTORY_JWT_AUDIENCE`.
- `GROWTH_FACTORY_JWT_KID`.
- `GROWTH_FACTORY_PRIVATE_KEY_FILE` ou secret equivalent hors Git.
- `GROWTH_FACTORY_ALLOWED_ADMIN_EMAILS` pour pilotes.

Growth Factory requis :

- `DATABASE_URL` PostgreSQL.
- `REDIS_URL` si workers actives.
- `STUDIO_ADMIN_API_ENABLED=true`.
- `STUDIO_ADMIN_JWT_PUBLIC_KEYS` avec `kid` staging/prod.
- `GROWTH_GENERATOR_MODE=simulated` pour pilote initial.
- `OLING_MODE=mock` ou `preview-only` tant que non certifie.
- `MAPSI_SITE_MODE=mock` ou `preview-only` tant que non certifie.
- flags publishers reels desactives par defaut.

Aucun secret n'est consigne dans ce rapport.

## 9. Installation OLING - Etape A inactive

1. Revue PR OLING et Growth Factory.
2. Sauvegarde applicative et base.
3. Deployer Growth Factory compatible.
4. Executer migrations Alembic.
5. Verifier `/healthz`.
6. Deployer OLING avec `GROWTH_FACTORY_ENABLED=0`.
7. Verifier que `/admin/growth` reste indisponible ou masque selon configuration.
8. Garder MAPSI Studio inchangé.

Decision Etape A : **GO conditionnel**.

## 10. Activation pilote - Etape B

Prealables :

1. Staging reseau certifie.
2. Inventaire historique read-only effectue.
3. JWT staging/prod valides.
4. `GROWTH_FACTORY_ALLOWED_ADMIN_EMAILS` limite aux pilotes.
5. Publishers externes en mock/preview-only.
6. Recette Playwright rejouee depuis navigateur reseau.

Activation :

1. Activer `GROWTH_FACTORY_ENABLED=1`.
2. Tester acces direct aux routes `/admin/growth/*` avec un admin non pilote : attendu `403`.
3. Tester admin pilote : consultation, creation controlee, generation simulee, preview, revue, approbation.
4. Garder publication reelle desactivee.

Decision Etape B : **NO-GO tant que staging reseau et inventaire ne sont pas certifies**.

## 11. Rollback

Retour immediat :

1. Repasser `GROWTH_FACTORY_ENABLED=0`.
2. Retirer temporairement `GROWTH_FACTORY_ALLOWED_ADMIN_EMAILS`.
3. Garder MAPSI Studio comme cockpit operationnel.
4. Stopper workers Growth si incident publication.
5. Ne supprimer aucune campagne ; archiver uniquement apres analyse.
6. Restaurer versions applicatives precedentes si incident bloquant.

## 12. Decisions separees

| Sujet | Decision |
| --- | --- |
| Installation production inactive | GO conditionnel apres revue PR/sauvegarde |
| Activation pilote admin OLING | NO-GO sans staging reseau |
| Publications reelles | NO-GO |
| Decommission MAPSI Studio | NO-GO |

## 13. Points bloquants restants

- `growth.oling.fr` non joignable depuis cet environnement.
- Aucun staging reseau autorise identifie.
- Aucun inventaire historique autorise.
- Connecteurs reels non verifies en sandbox/preview-only.
- Validation metier finale non realisee.
