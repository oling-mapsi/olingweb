# I18N-3 - SitePage public FR switch

Date : 2026-10-02

## Architecture avant

```text
Controller
-> SitePage
-> champs legacy
-> Twig
```

Les rendus publics lisaient directement `SitePage.title`, `metaDescription`, `heroTitle`, `heroIntro`, `heroSideHtml` et `bodyHtml`.

## Architecture apres

```text
Controller / service
-> SitePage trouvee par le slug legacy existant
-> LocalizedContentResolver
-> SitePageTranslation(fr)
-> SitePagePublicView
-> Twig
```

La resolution d'URL reste volontairement hybride :

- slug public FR resolu via `SitePage.slug`, pour ne pas modifier le routing ;
- contenu public FR rendu depuis `SitePageTranslation(fr)`.

## Controleurs et services modifies

- `PublicSitePageResolver`
- `SeoLandingController`
- `SeoResourceController`
- `PracticeController`
- `SeoGeoInternalLinkService`
- `LocalizedContentResolver`

## Templates publics modifies

Aucun template public n'a ete bascule massivement. Les templates continuent a recevoir une variable `page`, mais il s'agit maintenant d'un `SitePagePublicView` pour les pages `SitePage` migrees.

## Source des champs

- title : `SitePageTranslation(fr).seoTitle ?: title`
- meta description : `SitePageTranslation(fr).seoDescription`
- hero badge : `SitePageTranslation(fr).heroBadge`
- hero title : `SitePageTranslation(fr).heroTitle`
- hero intro : `SitePageTranslation(fr).heroIntro`
- hero side HTML : `SitePageTranslation(fr).heroSideHtml`
- body HTML : `SitePageTranslation(fr).bodyHtml`

Champs communs conserves sur `SitePage` :

- hero image
- canonical URL
- categories/tags
- publication date

## Lectures legacy supprimees

Les chemins publics suivants ne lisent plus les champs editoriaux legacy pour leur rendu `SitePage` :

- home/page editoriale/hubs via `PublicSitePageResolver`
- landing pages SEO via `SeoLandingController`
- index et articles ressources via `SeoResourceController`
- cartes ressources homepage via `PracticeController`
- maillage SEO geographique via `SeoGeoInternalLinkService`

Lectures legacy restantes justifiees :

- resolution de slug `SitePage.slug` pour conserver les URL FR ;
- champs communs non linguistiques (`heroImage`, `canonicalUrl`, categories/tags, publicationDate) ;
- services non front-rendu comme indexation chat, Growth preview ou workflow admin.

## Comparaison baseline

URLs controlees :

- `/`
- `/amoa-si`
- `/erp-progiciel`
- `/rgpd`
- `/facturation-electronique-amoa`
- `/ressources`

Resultat :

- HTTP status : identique
- `<title>` : identique
- meta description : identique
- canonical : identique
- robots : identique
- H1 : identique
- hreflang : identique
- JSON-LD hash : identique
- OpenGraph hash : identique

Difference technique :

- `body_hash` et `html_hash` changent en environnement `dev` a cause du HTML de toolbar/profiler genere entre deux captures.
- Aucun ecart detecte sur les champs SEO/contenu extraits.

## Tests

```text
./vendor/bin/phpunit
117 tests
336 assertions
PASS
```

Test source-of-data ajoute :

- legacy `SitePage` contient `LEGACY ...`
- `SitePageTranslation(fr)` contient `TRANSLATED FR ...`
- le resolver public retourne les valeurs de traduction
- le legacy n'apparait pas dans le resultat

## Gaps restants

- Les routes publiques EN/ES ne sont toujours pas activees.
- Les autres entites editoriales (`Practice`, `Services`, `Projet`, `Team`, `LegalPage`, `HomeSection`, `HomeCard`) restent hors perimetre.
- La suppression des colonnes legacy `SitePage` reste interdite a ce stade.
- Le sitemap continue d'utiliser le comportement existant.

## Prochaine etape

I18N-4 : generalisation du modele `Entity + Translation` aux autres contenus structurants du front public.

