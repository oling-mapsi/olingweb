# I18N-1 - SitePage translation foundation

Date : 2026-10-02

## Baseline

- Base commit : `1fddd7a9aa3af779dc807f783559daf77d5b45a8`
- `site_page` avant migration locale : 70 lignes
- Routes FR inspectees : `/`, `/amoa-si`, `/erp-progiciel`, `/rgpd`, `/facturation-electronique-amoa`, `/ressources`

## Modifications

- Ajout de `SitePageTranslation`.
- Ajout de la relation `SitePage` -> `SitePageTranslation`.
- Ajout de `SitePageTranslationRepository`.
- Ajout de `TranslationSourceHasher`.
- Ajout de `LocalizedContentResolver` minimal, non branche au front.
- Ajout de `LocalizedSlugHistory` passif pour preparer les futures redirections.
- Ajout de la migration `Version20261002120000`.
- Ajout de tests unitaires hash, workflow et obsolescence.

## Modele reel retenu

Les champs historiques de `SitePage` restent en place pour compatibilite front et BO.

Champs copies vers `SitePageTranslation(fr)` :

- `slug`
- `title`
- `metaDescription` vers `seoDescription`
- `title` vers `seoTitle`
- `heroBadge`
- `heroTitle`
- `heroIntro`
- `heroSideHtml`
- `bodyHtml`
- `publishedAt`
- `unpublishedAt`

Champs non copies dans ce lot :

- `heroImage` : media commun conserve sur `SitePage`
- `externalId`, `sourceCampaignId`, `authorDisplayName`, `publicationDate`, `categories`, `tags`, `canonicalUrl` : conserves sur `SitePage` pour compatibilite Growth/public ; leur localisation sera arbitree dans les lots suivants.

## Contraintes

`site_page_translation` :

- `UNIQUE(site_page_id, locale)`
- `UNIQUE(locale, slug)`
- FK `site_page_id` vers `site_page(id)` avec `ON DELETE CASCADE`
- FK `reviewed_by_id` vers `app_user(id)` avec `ON DELETE SET NULL`

`localized_slug_history` :

- index de lookup `(resource_type, resource_id, locale, old_slug)`
- FK `changed_by_id` vers `app_user(id)` avec `ON DELETE SET NULL`

Strategie slug : `UNIQUE(locale, slug)` est compatible pour `SitePage`, car `site_page.slug` etait deja unique. Les autres types de contenus ne sont pas encore migres.

## Workflow

Statuts autorises :

```text
draft
ai_translated
to_review
reviewed
published
```

`outdated` n'est pas un statut. L'obsolescence est calculee par comparaison de hash.

## Hash source

Service : `TranslationSourceHasher`.

Hash SHA-256 deterministe sur les champs editoriaux significatifs de `SitePage` :

- slug
- title
- metaDescription
- heroBadge
- heroTitle
- heroIntro
- heroSideHtml
- bodyHtml
- canonicalUrl
- categories
- tags

Exclus : ids, timestamps techniques, publicationStatus, publishedAt, unpublishedAt.

## Migration

La migration cree :

- `site_page_translation`
- `localized_slug_history`

Elle insere uniquement les traductions FR :

```text
COUNT(site_page) = 70
COUNT(site_page_translation WHERE locale = 'fr') = 70
```

Aucune traduction EN/ES n'est generee.

## SitePageRevision

`SitePageRevision` conserve aujourd'hui les revisions Growth/resources : titre, slug, excerpt, contenu HTML, meta, canonical, image, categories, tags, statut et auteur.

Dans ce lot, il n'est pas modifie afin d'eviter deux refontes simultanees.

Strategie recommandee pour un lot ulterieur :

- conserver `SitePageRevision` pour les revisions globales existantes tant que le front lit `SitePage` ;
- creer ensuite `SitePageTranslationRevision` ou ajouter `locale` a un modele de revision localise quand l'edition multilingue BO sera activee ;
- ne pas melanger des revisions non localisees et localisees dans un meme workflow public.

## Tests

- `./vendor/bin/phpunit tests/SitePageTranslationTest.php` : OK
- `./vendor/bin/phpunit` : OK, 110 tests, 312 assertions
- `php bin/console doctrine:schema:validate --skip-sync` : mapping OK
- Contraintes DB verifiees localement :
  - doublon `site_page_id + locale` rejete
  - doublon `locale + slug` rejete

## Ecarts par rapport au document initial

- `framework.default_locale` non modifie, conformement au lot I18N-1.
- Aucun routing `/en` ou `/es` ajoute.
- Aucun canonical, hreflang, sitemap ou JSON-LD public modifie.
- `sourceContentHash` reste nullable en migration initiale ; le calcul applicatif est en place pour les traductions futures.

## Prochain lot recommande

I18N-2 : synchronisation BO minimale `SitePage` <-> `SitePageTranslation(fr)`, puis preview interne des traductions sans activation publique.

