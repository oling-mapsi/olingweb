# OLING SEO/GEO P1A - Recette d'integration admin

Date recette : 2026-10-14

## Perimetre

Correctif teste : edition back-office des narratives SEO avancees, separee de toute publication des contenus SEO P1A.

Pages testees : `/cyber-securite`, `/conseil-qualite`, `/crm`, `/gmao`, `/si-finance`.

Services testes : `/business-apps/erp`, `/consulting/reforme-facturation-electronique-amoa`.

## Environnement

MySQL local/staging indisponible sur la machine de recette : pas de client `mysql`, pas de `docker`, ports MySQL locaux fermes. Aucune connexion production n'a ete utilisee.

Recette executee sur SQLite isole :

- DB : `/private/tmp/oling_p1a_admin_recette_20261014_final8.sqlite`
- snapshots DB : `/private/tmp/oling-p1a-recette/db/before-final8.json`, `/private/tmp/oling-p1a-recette/db/after-final8.json`
- comparaison : `/private/tmp/oling-p1a-recette/db/compare-final8.json`
- rapport navigateur : `/private/tmp/oling-p1a-recette/browser-report-final8.json`
- captures : `/private/tmp/oling-p1a-recette/screenshots/page-cyber-securite-seo-avance.png`, `/private/tmp/oling-p1a-recette/screenshots/service-erp-seo-avance.png`

## Corrections apportees pendant recette

- `ServicesType` utilise `is_callable()` pour les getters/setters dynamiques du DTO `StructuredContentAdminFormData`.
- `StructuredContentTranslationSynchronizer::upsert()` n'utilise plus `ON DUPLICATE KEY UPDATE`; remplacement par `update` puis `insert` DBAL portable avec reprise en cas de contrainte unique.
- `templates/services.html.twig` utilise les getters du `TranslatedEntityPublicView` pour eviter les acces Twig directs non resolus.
- `scripts/seo_p1a_admin_recette.mjs` couvre la recette navigateur complete et produit un rapport JSON.

## Resultats navigateur

OK :

- Connexion administrateur de test.
- Redirection login hors session.
- Affichage `Contenu SEO avance` pages et services.
- Champs pre-remplis.
- Modification, sauvegarde, rechargement et persistance.
- Rendu public Twig des H1 modifies.
- Rejet format malforme `citablePassages`.
- Refus de sauvegarde avec hash perime.
- Sauvegarde d'un champ service non SEO sans perte de narrative.
- Protection CSRF par token invalide : valeur non sauvegardee.

## Preservation DB

Verifie dans `/private/tmp/oling-p1a-recette/db/compare-final8.json` :

- `homePage`, `corePage`, `otherBranch` conserves sur les pages.
- `citablePassages`, `supportLinks`, `anonymousProofs`, `unknownBlock` conserves pages et services.
- Slugs, canonical URLs, statuts de publication et images pages conserves.
- Le test concurrent conserve la valeur de l'administrateur B sur `/crm` et refuse l'ecrasement stale de l'administrateur A.

## Tests techniques

- `php bin/phpunit --do-not-cache-result tests/SeoStructuredContentAdminMapperTest.php` : OK, 2 tests, 12 assertions.
- `php bin/phpunit --do-not-cache-result tests/SitePageTranslationTypeTest.php` : OK, 1 test, 2 assertions.
- `php bin/console doctrine:schema:validate --skip-sync` : mapping OK.
- `php bin/console lint:twig templates/admin/pages/edit.html.twig templates/admin/services/_form.html.twig templates/services.html.twig` : OK.
- `git diff --check` : OK.

## Fichiers a inclure dans la PR admin

- `src/Dto/StructuredContentAdminFormData.php`
- `src/Form/SitePageTranslationType.php`
- `src/Form/ServicesType.php`
- `src/Service/SeoStructuredContentAdminMapper.php`
- `src/Service/StructuredContentTranslationSynchronizer.php`
- `templates/admin/pages/edit.html.twig`
- `templates/admin/services/_form.html.twig`
- `templates/services.html.twig`
- `tests/SitePageTranslationTypeTest.php`
- `tests/SeoStructuredContentAdminMapperTest.php`
- `scripts/seo_p1a_admin_recette.mjs`
- `OLING_SEO_P1A_ADMIN_PUBLICATION_CIRCUIT.md`
- `OLING_SEO_P1A_RECETTE_INTEGRATION.md`

## Deploiement limite

1. Deployer uniquement le correctif d'administration.
2. Ne pas publier les contenus SEO P1A dans le meme lot.
3. Avant production, refaire une smoke recette sur staging MySQL si l'environnement devient disponible.
4. Apres deploiement, verifier `/admin/pages`, `/admin/services`, puis rendu public d'une page et d'un service sans modifier les contenus metier.

## Rollback

Rollback applicatif : revert du commit de PR admin uniquement. Ne pas utiliser `git restore .` ou restauration globale du worktree.

Rollback DB : aucune migration de contenu n'est prevue par ce lot. Si une saisie admin de test etait faite hors recette locale, restaurer champ par champ depuis l'export avant modification, sans backfill global.

## Decision

GO conditionnel pour deploiement du seul correctif d'administration, sous reserve d'une validation staging MySQL courte avant mise en production.

NO-GO pour publication des contenus SEO P1A : cette recette ne publie aucun contenu editorial.
