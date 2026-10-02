# I18N-2 - SitePage admin FR source of truth

Date : 2026-10-02

## Architecture BO avant

LIST :

- `SitePageAdminController::index`
- liste les `SitePage` hors `home`
- affiche `slug` et `title`
- cree les pages manquantes via `ensureManagedPages`

CREATE :

- pas de route explicite de creation libre
- creation implicite des pages gerees dans `ensureManagedPages`

EDIT :

- `SitePageAdminController::edit`
- formulaire `SitePageType`
- edition directe des champs legacy `SitePage`

SAVE :

- upload hero optionnel
- `EntityManager::flush`
- aucun service de synchronisation

REVISION :

- aucun `SitePageRevision` cree par l'ecran admin pages
- `SitePageRevision` reste utilise par Growth/resources

PREVIEW :

- pas de preview interne par locale

PUBLICATION :

- basee sur les champs legacy `SitePage.publicationStatus`, `publishedAt`, `unpublishedAt`

## Architecture BO apres

SOURCE DE VERITE :

```text
SitePageTranslation(locale=fr)
```

COMPATIBILITE LEGACY :

```text
SitePageTranslation(fr) -> SitePage legacy
```

L'ecran d'edition admin utilise maintenant `SitePageTranslationType` sur la traduction FR. Les champs legacy `SitePage` restent conserves et synchronises automatiquement pour le front public existant.

## Synchronisation legacy

Service central :

```text
SitePageTranslationSynchronizer
```

Responsabilites :

- garantir une traduction FR via `ensureFrenchTranslation`
- recopier la traduction FR vers `SitePage`
- mettre a jour `publicationStatus`, `publishedAt`, `unpublishedAt`
- recalculer `sourceContentHash`
- creer un `LocalizedSlugHistory` lors d'un changement de slug FR publie

Sens unique :

```text
FR translation -> SitePage legacy
```

Pas de synchronisation permanente bidirectionnelle.

## Formulaire BO

Onglets prepares :

- FR editable
- EN indicateur/placeholder
- ES indicateur/placeholder

Champs FR :

- statut
- slug
- title
- meta description
- hero badge
- hero title
- hero intro
- hero side HTML
- body HTML
- dates publication/depublication
- image hero commune `SitePage`

Validation :

- collision `locale + slug` detectee par le FormType avant l'erreur SQL
- validation JSON conservee pour les modes `home`, `editorial`, `seo`, `structured`

## Slug history

Un changement de slug FR publie cree :

```text
resourceType = App\Entity\SitePage
resourceId = page id
locale = fr
oldSlug
newSlug
changedAt
changedBy
```

Aucune redirection publique n'est activee dans ce lot.

## Preview interne

Route :

```text
/admin/pages/{id}/preview/{locale}
```

Caracteristiques :

- protegee par `^/admin` donc `ROLE_ADMIN`
- accepte une traduction non publiee
- `X-Robots-Tag: noindex, nofollow`
- meta robots noindex via le layout admin existant
- ne modifie pas le routing public

La preview rend un contexte visuel proche du front, sans activer les URLs publiques EN/ES.

## SitePageRevision

Le comportement est preserve :

- l'ecran admin pages ne creait pas de revision avant I18N-2
- il n'en cree toujours pas
- `SitePageRevision` represente aujourd'hui du contenu legacy FR lie a Growth/resources

Limite documentee :

- un lot futur devra definir `SitePageTranslationRevision` ou un modele de revision localise avant l'edition EN/ES avancee.

## Tests

- `./vendor/bin/phpunit tests/SitePageTranslationTest.php`
- `./vendor/bin/phpunit tests/SitePageTranslationSynchronizerTest.php`
- `./vendor/bin/phpunit tests/SitePageTranslationTypeTest.php`
- `./vendor/bin/phpunit`

Resultat :

```text
116 tests
330 assertions
PASS
```

## Non-regression

Inchange :

- `framework.default_locale`
- routes publiques FR
- canonical public
- hreflang public
- sitemap public
- JSON-LD public
- generation EN/ES
- push/deploy

Counts applicatifs apres lot :

```text
SitePage = 70
SitePageTranslation(fr) = 70
SitePageTranslation(en) = 0
SitePageTranslation(es) = 0
```

## Prochaine etape

I18N-3 : bascule du front FR `SitePage` vers `SitePageTranslation(fr)` avec rendu strictement identique.

