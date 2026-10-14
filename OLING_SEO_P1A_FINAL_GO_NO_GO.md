# OLING SEO/GEO P1A - Validation finale MySQL et GO/NO-GO

Date : 2026-10-14

## 1. Resultats MySQL

Environnement utilise : MySQL local isole 8.0.30, datadir temporaire `/private/tmp/oling-p1a-mysql`, port `127.0.0.1:3310`. Aucune connexion production.

Resultat : OK.

Controles passes :

- creation et modification des traductions `service_translation`, `practice_translation`, `projet_translation`, `team_translation`, `legal_page_translation`, `home_section_translation` ;
- nouvel upsert DBAL : insert puis update sans doublon ;
- contrainte unique `(service_id, locale)` verifiee ;
- transaction + rollback sur narrative service : OK ;
- ecritures concurrentes sur champs distincts : OK ;
- type MySQL `JSON` sur `service_translation.public_narrative` : JSON valide, sauvegarde et relecture OK ;
- narrative SEO service relue avec `headline` attendu.

Preuve locale : `/private/tmp/oling-p1a-mysql/report.json`.

## 2. Non-regression synchroniseur

Suite complete PHPUnit executee :

`APP_ENV=test ... php bin/phpunit --do-not-cache-result`

Resultat : OK, 328 tests, 2173 assertions.

Validations complementaires :

- `doctrine:schema:validate --skip-sync` : OK ;
- `lint:twig templates/admin/pages/edit.html.twig templates/admin/services/_form.html.twig templates/services.html.twig` : OK ;
- `git diff --check` : OK.

Le changement de `StructuredContentTranslationSynchronizer::upsert()` a ete valide sur services, practices, projets, equipes, pages institutionnelles et sections d'accueil.

## 3. Non-regression services publics

Pages testees sur MySQL local :

| URL | HTTP | Title | H1 | JSON-LD |
| --- | ---: | --- | --- | ---: |
| `/business-apps/erp` | 200 | `Meta title erp` | `ERP et progiciels - MySQL public` | 2 |
| `/consulting/reforme-facturation-electronique-amoa` | 200 | `Meta title reforme-facturation-electronique-amoa` | `RFE AMOA - MySQL public` | 2 |

Preuve locale : `/private/tmp/oling-p1a-mysql/public-services-report.json`.

Delta avant/apres :

- avant correction Twig : le template `services.html.twig` accedait directement a `service.designation`, `service.description`, `service.projets`, `service.teams`, incompatible avec `TranslatedEntityPublicView` dans ce circuit ;
- apres correction : utilisation explicite des getters, rendu public HTTP 200 et contenu editorial attendu conserve.

Les 404 releves dans le rapport viennent du menu global avec fixture minimale incomplete. Ils ne concernent pas les routes services testees ni le correctif P1A.

## 4. Fichiers exacts du correctif

A inclure dans le commit applicatif P1A :

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
- `OLING_SEO_P1A_ADMIN_PUBLICATION_CIRCUIT.md`
- `OLING_SEO_P1A_RECETTE_INTEGRATION.md`
- `OLING_SEO_P1A_FINAL_GO_NO_GO.md`

A exclure du commit applicatif P1A :

- exports de production et matrices de reconciliation ;
- bases, snapshots et rapports sous `/private/tmp` ;
- fichiers JSON d'enrichissement SEO P1 ;
- scripts temporaires de recette ;
- travaux Growth ou audits hors correctif admin.

## 5. Risques residuels

- Les tables de traduction DBAL ne sont pas toutes gerees par Doctrine ORM ; leur presence doit etre confirmee en staging/prod avant deploiement.
- La recette publique MySQL utilise une fixture minimale : les 404 du menu global ne sont pas exploitables comme regression applicative.
- Le correctif ne publie aucun contenu SEO P1A ; il active seulement l'edition securisee.

## 6. Deploiement et rollback

Deploiement :

1. Deployer uniquement le commit applicatif P1A.
2. Ne lancer aucun backfill, import ou publication SEO.
3. Verifier `/admin/pages` et `/admin/services`.
4. Modifier un champ test en staging, verifier persistance DB et rendu public.
5. En production, faire uniquement une smoke lecture/affichage avant toute saisie editoriale.

Rollback applicatif :

1. Revert du commit P1A uniquement.
2. Ne pas utiliser de restauration Git globale.
3. Purger le cache Symfony.
4. Verifier que les pages services publiques repondent toujours.

Rollback DB :

- aucune migration de contenu n'est livree dans ce lot ;
- si une saisie admin est effectuee apres deploiement, restaurer champ par champ depuis l'export pre-saisie, sans backfill global.

## 7. Decision

GO pour le deploiement du seul correctif d'administration P1A.

NO-GO pour la publication des contenus SEO P1A dans ce lot.
