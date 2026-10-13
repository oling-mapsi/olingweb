# Growth Phase 6 - Certification preproduction

Date : 2026-10-13

## Decision

**GO pour preproduction isolee reproductible. NO-GO production directe.**

Aucun deploy, push, merge, envoi email, publication externe ou modification production n'a ete realise.

## 1. Etat Git consolide

OLING Web :

- Worktree : `/Users/florestanrouet/.codex/worktrees/growth-oling-admin/olingw`
- Branche : `codex-growth-oling-admin-integration`
- Base distante : `origin/main` = `e8336261`
- Ecart avant commit du rapport : `0` commit en retard, `3` commits Growth en avance
- Commits Growth applicatifs : `94acaf2a`, `6be3c08c`, `b548efce`
- Commit Phase 6 documentation/scripts : cree localement apres ce rapport

Growth Factory :

- Branche : `codex-studio-simple-growth-final`
- Base distante : `origin/main` = `460beb8`
- Commit Phase 6 : `d52240c Certify simple Growth preproduction controls`
- Modifications locales non liees conservees non stagees.

## 2. Architecture retenue

- OLING Admin reste l'interface Growth unique via `/admin/growth`.
- Growth Factory reste moteur metier et source de verite.
- Communication serveur-a-serveur JWT RS256, aucun secret expose au navigateur.
- Feature flag OLING : `GROWTH_FACTORY_ENABLED`, desactive par defaut hors recette.
- Generateur : `GROWTH_GENERATOR_MODE=simulated` en preproduction isolee.
- Publishers : modes `mock` uniquement pour la certification.

## 3. Configuration staging

Aucun staging reseau Growth exploitable n'a ete trouve dans les docs/env inspectes. La certification utilise donc un staging local representatif :

- PostgreSQL : `mapsi_growth_phase6_staging`
- Growth Factory : FastAPI local
- OLING Web : Symfony local
- IA : simulated
- Publishers : mock/sandbox
- Scripts ajoutes :
  - `bin/growth-phase6-env`
  - `bin/growth-phase6-reset`
  - `bin/growth-phase6-health`
  - `bin/growth-phase6-stop`

Les scripts de reset refusent les noms de base ne contenant pas `phase6`, `staging` ou `test`.

## 4. Migrations

Commande executee sur PostgreSQL local isole :

```bash
DATABASE_URL=postgresql+psycopg://florestanrouet@localhost:5432/mapsi_growth_phase6_staging alembic upgrade head
```

Resultat : OK jusqu'a `20260726_0024_feature_catalog_navigation_labels`.

## 5. Contrat API `/studio-simple/*`

Valide :

- creation campagne ;
- generation ;
- edition asset avec `expected_version` ;
- conflit `409` sur version obsolete ;
- revue ;
- approbation ;
- refus publication sans approbation ;
- publication partielle puis finale ;
- depublication ciblee par canal sans toucher les autres canaux ;
- archivage ;
- suppression logique `DELETED` pour campagnes jamais publiees ;
- statuts campagne/assets.

Erreurs validees automatiquement : `401`, `403`, `404`, `409`, `422`.  
`400`, `429`, `5xx` et perte reseau restent documentes/geres cote clients, mais non rejoues en serveur reel Phase 6.

## 6. Controle XSS

Correction Phase 6 Growth Factory :

- extraction d'un service central `HtmlSanitizer` ;
- sanitation appliquee aux HTML generes et aux HTML edites ;
- tags bloques : `script`, `style`, `iframe`, `object`, `embed` ;
- attributs `on*` bloques ;
- protocoles `javascript:`, `data:`, `vbscript:` bloques, y compris variantes avec espaces/sauts de ligne ;
- liens `_blank` forces avec `rel="noopener noreferrer"` si necessaire.

Tests XSS : OK.

## 7. Publishers

Certification executee uniquement avec publishers simules :

- `oling_site` mock ;
- `mapsi_site` mock ;
- `linkedin_manual` manuel ;
- `email_manual` manuel.

Valide : publication simulee, refus sans approbation, conflit de version, publication partielle, absence de double publication via asset deja publie, depublication explicite.  
Non execute : publication reelle OLING/MAPSI, LinkedIn, email.

## 8. Inventaire donnees existantes

Aucun acces autorise a un referentiel historique Growth Factory de production n'a ete utilise.  
Inventaire certifie uniquement sur base isolee synthetique : donnees recreees pour tests, aucune campagne client.

Conclusion : OLING consomme le meme referentiel Growth Factory dans cette architecture ; aucune reprise de donnees OLING n'est requise.

## 9. Tests executes

| Perimetre | Commande | Resultat |
| --- | --- | --- |
| Growth Factory SQLite/test | `python3 -m pytest tests/integration/test_simple_campaign_api.py tests/unit/test_simple_campaign_service.py tests/unit/test_html_sanitizer.py -q` | OK, 13 tests |
| Growth Factory PostgreSQL migration | `DATABASE_URL=postgresql+psycopg://florestanrouet@localhost:5432/mapsi_growth_phase6_staging alembic upgrade head` | OK |
| Growth Factory suite ciblee avec env PostgreSQL | `DATABASE_URL=postgresql+psycopg://florestanrouet@localhost:5432/mapsi_growth_phase6_staging GROWTH_GENERATOR_MODE=simulated python3 -m pytest ... -q` | OK, 13 tests ; fixtures pytest restent en SQLite memoire |
| OLING lint | `php -l src/Controller/GrowthAdminController.php ...` | OK |
| OLING Growth | `./vendor/bin/phpunit --do-not-cache-result tests/GrowthFactoryClientTest.php tests/GrowthFactoryViewTest.php` | OK, 7 tests |
| OLING Analytics | `./vendor/bin/phpunit --do-not-cache-result tests/AnalyticsTrackingTest.php` | OK, 7 tests |
| OLING agent IA | `./vendor/bin/phpunit --do-not-cache-result tests/ChatConversationManagerTest.php` | OK, 3 tests |
| OLING contact/admin | `./vendor/bin/phpunit --do-not-cache-result tests/ContactControllerTest.php` | OK, 5 tests |

La recette Playwright Phase 5 reste le dernier parcours navigateur complet execute ; Phase 6 n'a pas relance de navigateur bout en bout apres ajout des scripts.

## 10. Matrice de parite

| Fonction | Etat |
| --- | --- |
| Cockpit simple MAPSI dans OLING | Operationnel |
| Types OLING / MAPSI_USERS / MAPSI_MARKETING | Operationnels |
| Canaux `mapsi_site`, `oling_site`, `linkedin_manual`, `email_manual` | Operationnels en mock/manual |
| Generation IA | Simulated certifie, OpenAI non rejoue |
| Edition/versionnement | Operationnel |
| Revue/approbation | Operationnel |
| Publication | Mock certifie, reel bloque hors prod |
| Depublication ciblee | Operationnelle |
| Suppression | Logique, jamais publiee uniquement |
| Archivage | Operationnel |
| Audit | Conserve Growth Factory |
| Weekly Packs / Source Packs / retry / schedule / channel settings | Conserves backend, ecrans OLING avances a planifier |

## 11. Risques residuels

- Pas de vrai staging reseau certifie.
- Publishers reels non executes volontairement.
- OpenAI reel non rejoue.
- Fixtures pytest Growth Factory forcent SQLite memoire ; PostgreSQL est certifie par migrations, pas par toute la suite fonctionnelle.
- Inventaire donnees historiques non realise faute d'acces autorise.

## 12. Runbook deploiement futur

Prealables :

1. Sauvegarder bases OLING et Growth Factory.
2. Installer secrets hors Git : JWT private key OLING, JWT public key Growth Factory, tokens publishers, OpenAI si active.
3. Deployer Growth Factory avant OLING.
4. Lancer Alembic `upgrade head`.
5. Verifier `/healthz` Growth Factory.
6. Garder `GROWTH_FACTORY_ENABLED=0` dans OLING.
7. Deployer OLING.
8. Verifier routes admin et authentification.
9. Activer en pilote `GROWTH_FACTORY_ENABLED=1` pour admins autorises.
10. Smoke-test : liste, creation campagne test, generation simulated/sandbox, preview, approbation, publication sandbox.

Rollback :

1. Repasser `GROWTH_FACTORY_ENABLED=0`.
2. Garder MAPSI Studio accessible.
3. Stopper les workers Growth si anomalie.
4. Revenir aux versions precedentes applicatives si necessaire.
5. Ne pas supprimer les campagnes Growth ; archiver uniquement apres diagnostic.

## 13. Decision production

Decision Phase 6 : **NO-GO production directe**.

Passage possible vers production uniquement apres :

- staging reseau isole certifie ;
- cles JWT dediees staging/prod validees ;
- recette Playwright Phase 6 rejouee ;
- inventaire read-only des donnees reelles autorise ;
- validation metier des publishers reels en sandbox ou preview-only ;
- revue securite finale.
