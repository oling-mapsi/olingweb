# Growth Phase 5 - Finalisation métier et préparation staging

Date : 2026-10-13

## Fonctions implémentées

- Suppression de l'auto-approbation après génération IA.
- Workflow simple : `DRAFT -> READY_FOR_REVIEW/GENERATED -> REVIEWED -> APPROVED -> PUBLISHED`.
- Revue et approbation humaines par asset/canal, avec acteur JWT, date et version approuvée.
- Invalidation d'approbation après modification de contenu.
- Publication refusée si l'asset ou sa version courante n'est pas approuvé.
- Suppression logique limitée aux campagnes jamais publiées, avec audit conservé.
- Archivage distinct, sans dépublication.
- Dépublication distincte, explicite, par canal.
- Générateur Growth configurable par `GROWTH_GENERATOR_MODE=simulated|openai`.
- Sanitation HTML serveur sur les contenus édités.
- OLING Admin expose revue, approbation, publication, dépublication, archivage et suppression séparées.

## Contrats API ajoutés ou modifiés

- `POST /studio-simple/campaigns/{campaign_id}/assets/{asset_id}/review`
- `POST /studio-simple/campaigns/{campaign_id}/assets/{asset_id}/approve`
- `POST /studio-simple/campaigns/{campaign_id}/archive`
- `POST /studio-simple/campaigns/{campaign_id}/unpublish`
- `DELETE /studio-simple/campaigns/{campaign_id}` : suppression logique `DELETED`, seulement si jamais publié ; la campagne devient ensuite inaccessible via l'API simple.
- `SimpleAssetResponse` ajoute `approved_by`, `approved_at`, `approved_version`.
- `expected_version` reste obligatoire côté OLING pour les actions sensibles par asset.

## Recette navigateur

Commande :

```bash
OLING_BASE_URL=http://127.0.0.1:8061 OLING_ADMIN_USERNAME=phase5 OLING_ADMIN_PASSWORD=testpass node tests/e2e/growth-phase5-recette.mjs
```

Résultat : OK.

Parcours rejoué :

- `OLING`, `MAPSI_USERS`, `MAPSI_MARKETING`.
- Canaux `oling_site`, `mapsi_site`, `linkedin_manual`, `email_manual`.
- Création, génération simulée, édition, conflit de version, preview, publication refusée sans approbation, revue, approbation, publication mock, suppression avant publication, archivage après publication.

Captures mises à jour :

- `docs/growth/screenshots/growth-index-desktop.png`
- `docs/growth/screenshots/growth-index-mobile.png`
- `docs/growth/screenshots/growth-oling-show.png`
- `docs/growth/screenshots/growth-preview.png`

## Tests exécutés

| Périmètre | Commande | Résultat |
| --- | --- | --- |
| Growth Factory SQLite/test | `python3 -m pytest tests/integration/test_simple_campaign_api.py tests/unit/test_simple_campaign_service.py -q` | OK, 10 tests avant ajout XSS |
| Growth Factory PostgreSQL staging | `DATABASE_URL=postgresql+psycopg://florestanrouet@localhost:5432/mapsi_growth_phase5_staging GROWTH_GENERATOR_MODE=simulated python3 -m pytest tests/integration/test_simple_campaign_api.py tests/unit/test_simple_campaign_service.py -q` | OK, 11 tests |
| OLING Growth client | `./vendor/bin/phpunit --do-not-cache-result tests/GrowthFactoryClientTest.php` | OK, 5 tests, 18 assertions |
| OLING Growth view | `./vendor/bin/phpunit --do-not-cache-result tests/GrowthFactoryViewTest.php` | OK, 2 tests, 6 assertions |
| OLING Analytics | `./vendor/bin/phpunit --do-not-cache-result tests/AnalyticsTrackingTest.php` | OK, 7 tests, 26 assertions |
| OLING agent IA commercial | `./vendor/bin/phpunit --do-not-cache-result tests/ChatConversationManagerTest.php` | OK, 3 tests, 6 assertions |
| OLING contact/admin | `./vendor/bin/phpunit --do-not-cache-result tests/ContactControllerTest.php` | OK, 5 tests, 17 assertions |
| Routes OLING Growth | `php bin/console debug:router --env=dev \| rg admin_growth` | OK |

## PostgreSQL staging local

Base dédiée : `mapsi_growth_phase5_staging`.

Commandes validées :

```bash
dropdb --if-exists mapsi_growth_phase5_staging
createdb mapsi_growth_phase5_staging
DATABASE_URL=postgresql+psycopg://florestanrouet@localhost:5432/mapsi_growth_phase5_staging alembic upgrade head
DATABASE_URL=postgresql+psycopg://florestanrouet@localhost:5432/mapsi_growth_phase5_staging alembic upgrade head
```

Résultat :

- migrations Alembic réelles OK sur base vide ;
- redémarrage sur base déjà migrée OK ;
- aucune adaptation SQLite ajoutée.

## Sécurité contenus

- Sanitation serveur ajoutée sur HTML édité : suppression `script/style/iframe/object/embed`, attributs `on*`, URL `javascript:` et `data:`.
- Preview OLING conservée en iframe sandbox.
- Actions sensibles OLING protégées par CSRF.
- Permissions Growth portées par JWT RS256 serveur-à-serveur.
- Test XSS synthétique OK : contenu nettoyé avant stockage/réponse.
- Aucun secret de production ni publication réelle utilisés.

## Matrice de parité

| Fonction | Etat Phase 5 |
| --- | --- |
| Campagnes simples | Opérationnel via Growth Factory |
| Génération IA | Opérationnel en mode `simulated`, réel via `openai` explicite |
| Edition/versionnement | Opérationnel avec `expected_version` |
| Revue humaine | Opérationnel par asset |
| Approbation humaine | Opérationnel par asset, versionnée |
| Publication | Opérationnelle en mock, refus sans approbation |
| Dépublication | Endpoint dédié, mock validé |
| Suppression | Logique `DELETED`, limitée aux campagnes jamais publiées, audit conservé |
| Archivage | Endpoint dédié, liste active filtrée |
| Weekly Packs / Source Packs / retry / schedule / channel settings | Conservés Growth Factory, écrans avancés à planifier |

## Etat Git

- OLING : branche `codex-growth-oling-admin-integration`, commit local Phase 5.
- Growth Factory : commit local `e1d688e Finalize simple Growth workflow for staging`.
- Growth Factory conserve des modifications non liées non stagées, préexistantes.
- Aucun push, merge ou déploiement.

## Ecarts restants

- L'interface OLING reste volontairement simple ; les écrans avancés Weekly Packs, Source Packs, retry, schedule et channel settings restent à exposer.
- La sanitation HTML est stricte et locale au flux simple ; une politique HTML centralisée serait préférable avant production.
- Les commandes de démarrage staging restent manuelles ; prévoir script `bin/growth-phase5-local` ou équivalent.
- Les publishers réels n'ont pas été sollicités.

## Décision

Décision : **GO pour recette de préproduction isolée**, **NO-GO production directe**.

Conditions avant production :

- rejouer la recette sur un vrai staging réseau ;
- valider les clés JWT dédiées staging/prod ;
- activer `GROWTH_FACTORY_ENABLED` uniquement par feature flag contrôlé ;
- vérifier les publishers réels en sandbox fournisseur ;
- finaliser le runbook rollback : désactivation flag OLING puis arrêt API Growth côté admin.
