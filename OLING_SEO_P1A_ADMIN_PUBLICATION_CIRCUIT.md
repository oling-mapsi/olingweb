# OLING SEO/GEO P1A - Correctif du circuit de publication admin

Date : 2026-10-13

## Perimetre du correctif

Objectif : permettre la saisie back-office des champs SEO structures utilises par le rendu public, sans SQL manuel et sans remplacement integral des JSON.

Hors perimetre : ecriture production, merge, push, deploiement, modification de slug, canonical, redirection ou rattachement public.

## Code ajoute ou modifie

### Administration pages SEO

- `src/Form/SitePageTranslationType.php`
- `templates/admin/pages/edit.html.twig`
- `src/Service/SeoStructuredContentAdminMapper.php`

Section ajoutee dans `/admin/pages` en mode SEO : `Contenu SEO avance`.

Champs exposes pour `site_page_translation.structured_data.landingNarrative` :

- `metaTitle`
- `metaDescription`
- `heroTitle`
- `heroIntro`
- `promise`
- `focus` : un point par ligne
- `citablePassages` : `titre | texte`
- `supportLinks` : `libelle | URL | description`

Les cles inconnues de `landingNarrative` sont conservees. Les branches soeurs de `structured_data` comme `homePage`, `corePage` et autres contenus structures sont conservees.

### Administration services

- `src/Form/ServicesType.php`
- `src/Dto/StructuredContentAdminFormData.php`
- `src/Service/StructuredContentTranslationSynchronizer.php`
- `templates/admin/services/_form.html.twig`

Section ajoutee dans `/admin/services` : `Contenu SEO avance`.

Champs exposes pour `service_translation.public_narrative` :

- `metaTitle`
- `metaDescription`
- `eyebrow`
- `headline`
- `intro`
- `promise`

Les cles non exposees de `public_narrative` sont conservees, notamment `citablePassages`, `supportLinks`, `anonymousProofs` et autres blocs metier.

## Securite et qualite

- Les formulaires admin conservent la protection CSRF Symfony existante via `form_start`.
- Les droits d'acces restent ceux des routes `/admin/pages` et `/admin/services` existantes.
- Le mapper fusionne champ par champ et ne remplace pas le JSON complet.
- Un hash cache detecte les ecrasements concurrents sur le bloc SEO avance : si le contenu a change depuis l'ouverture du formulaire, la sauvegarde est refusee et l'admin doit recharger.
- Le synchroniseur services n'ecrit `public_narrative` que si le DTO contient une valeur existante ou editee ; il ne force pas `{}` sur un contenu absent.
- Aucun champ slug/canonical/redirection n'a ete ajoute.

## Correspondance Twig

Pages SEO (`templates/seo/_db-landing.html.twig`) :

- `metaTitle`
- `metaDescription`
- `heroTitle`
- `heroIntro`
- `promise`
- `focus`
- `citablePassages` attend des objets `{title, text}`
- `supportLinks` attend des objets `{label, href, description}`

Services (`templates/services.html.twig`) :

- `metaTitle`
- `metaDescription`
- `eyebrow`
- `headline`
- `intro`
- `promise`
- Les tableaux non exposes restent conserves pour une phase ulterieure.

## Tests executes

```bash
php -l src/Service/SeoStructuredContentAdminMapper.php
php -l src/Form/SitePageTranslationType.php
php -l src/Form/ServicesType.php
php -l src/Service/StructuredContentTranslationSynchronizer.php
php -l src/Dto/StructuredContentAdminFormData.php
php -l tests/SeoStructuredContentAdminMapperTest.php
php bin/phpunit --do-not-cache-result tests/SeoStructuredContentAdminMapperTest.php
php bin/phpunit --do-not-cache-result tests/SitePageTranslationTypeTest.php
git diff --check -- src/Service/SeoStructuredContentAdminMapper.php src/Form/SitePageTranslationType.php src/Form/ServicesType.php src/Service/StructuredContentTranslationSynchronizer.php src/Dto/StructuredContentAdminFormData.php templates/admin/pages/edit.html.twig templates/admin/services/_form.html.twig tests/SitePageTranslationTypeTest.php tests/SeoStructuredContentAdminMapperTest.php
APP_ENV=test DATABASE_URL=sqlite:///:memory: php bin/console lint:twig templates/admin/pages/edit.html.twig templates/admin/services/_form.html.twig
```

Resultat : OK.

Tests ajoutes :

- `tests/SeoStructuredContentAdminMapperTest.php`
  - preservation des cles inconnues et branches soeurs de `structured_data`
  - preservation des champs non exposes de `public_narrative`
- `tests/SitePageTranslationTypeTest.php`
  - adaptation du constructeur du formulaire avec le mapper

## Recette navigateur locale

Non finalisee dans ce lot : aucune base locale de recette peuplee n'a ete fournie pour ouvrir les pages admin avec leurs contenus reels.

Le lint Twig a ete valide hors sandbox avec une base SQLite memoire :

```bash
APP_ENV=test DATABASE_URL=sqlite:///:memory: php bin/console lint:twig templates/admin/pages/edit.html.twig templates/admin/services/_form.html.twig
```

Captures formulaire non produites. A realiser apres lancement local sur base de recette isolee.

## Procedure de publication P1A via admin

1. Deployer uniquement le correctif d'administration apres validation.
2. Verifier que les commandes de backfill JSON ne sont pas executees avec `--overwrite`.
3. Ouvrir `/admin/pages` pour les pages SEO du lot A :
   - `/cyber-securite`
   - `/conseil-qualite`
4. Renseigner les champs de `Contenu SEO avance` selon `OLING_SEO_P1A_CHANGEMENTS_VALIDABLES.md`.
5. Ouvrir `/admin/services` pour les services du lot A :
   - `/business-apps/erp`
   - `/consulting/reforme-facturation-electronique-amoa`
6. Renseigner les champs de `Contenu SEO avance` selon `OLING_SEO_P1A_CHANGEMENTS_VALIDABLES.md`.
7. Verifier le rendu public HTML avant/apres : HTTP 200, title, meta description, H1, canonical, robots, liens internes.
8. Ne pas saisir les champs non mappes, les champs identiques, les changements AMOA structurels ni les enrichissements CRM/GMAO/SI Finance sans validation separee.

## Plan de deploiement du seul correctif admin

1. Inclure uniquement les fichiers applicatifs et tests listés dans ce document.
2. Exclure les exports production et fichiers `var/`.
3. Executer les tests PHP et la recette navigateur sur base isolee.
4. Deployer le correctif admin seul.
5. Verifier l'affichage des sections `Contenu SEO avance`.
6. Publier manuellement le lot P1A dans l'admin apres validation metier.

## Rollback du correctif admin

Rollback Git du correctif applicatif :

```bash
git revert --no-commit <sha_commit_correctif_admin_p1a>
```

Ne pas utiliser `git restore .`, `git reset --hard` ou une restauration globale du worktree.

Rollback contenu P1A apres saisie admin :

- utiliser `OLING_SEO_P1A_ROLLBACK.md`
- restaurer champ par champ les anciennes valeurs issues de `OLING_SEO_PROD_CONTENT_MATRIX_RECONCILED.csv`
- verifier ensuite le HTML public des URL modifiees

## Decision

NO-GO production pour l'instant : code et tests unitaires OK, mais recette navigateur locale et captures non executees dans ce sandbox.
