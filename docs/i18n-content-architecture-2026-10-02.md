# OLING.FR - Architecture contenu en base et i18n FR / EN / ES

Date : 2026-10-02

## Recommandation

Retenir une architecture Doctrine maison `Entity` + `EntityTranslation`, sans nouvelle dependance.

Raisons :

- le projet a deja des entites editorialisees (`SitePage`, `Practice`, `Services`, `Projet`, `Team`, `LegalPage`, `HomeSection`, `HomeCard`, etc.) ;
- les slugs doivent etre differents par langue, resolubles, historises et SEO, ce qui depasse un simple champ JSON ;
- le back-office doit afficher les statuts par langue, les validations humaines et les traductions obsoletes ;
- Symfony Translation doit rester limite a la microcopy applicative ;
- Stof/DoctrineExtensions est deja present pour `sluggable`, mais son volet translatable/loggable ne couvre pas proprement le workflow editorial demande.

Ne pas utiliser JSON multilingue pour les contenus principaux : trop faible pour les contraintes d'unicite, les requetes BO, les sitemaps, hreflang, publication independante et historique.

Ne pas ajouter de bundle tiers i18n sans besoin bloqueur.

## Constats existants

- `config/packages/translation.yaml` est actuellement en `default_locale: en`; cible : `fr`.
- `templates/base.html.twig` genere un canonical/hreflang uniquement sur l'URL courante, sans alternates reels.
- `SitePage` contient deja slug, title, meta, body, statut, canonical et ressources.
- `SitePageRevision` fournit une base de versioning reusable, mais non localisee.
- `Practice`, `Services`, `Projet`, `Team`, `LegalPage` portent encore directement des champs textuels FR.
- `PublicSiteController`, `SeoLandingController`, `SitemapSubscriber` resolvent les pages par slug FR ou listes codees.
- Plusieurs templates gardent du contenu editorial FR en dur.

## Modele cible

Locales supportees :

```text
fr
en
es
```

Convention URL :

- FR sans prefixe : `/erp-progiciel`
- EN avec prefixe : `/en/erp-consulting`
- ES avec prefixe : `/es/consultoria-erp`

Chaque entite editorialisee garde les donnees communes dans l'entite racine et les champs linguistiques dans une table de traduction.

Exemple `SitePage` :

```text
SitePage
- id
- type
- template
- featuredImage
- sourceLocale = fr
- status technique
- sortOrder
- createdAt
- updatedAt
```

```text
SitePageTranslation
- id
- sitePage
- locale
- title
- slug
- heroBadge
- heroTitle
- heroIntro
- bodyHtml
- seoTitle
- seoDescription
- ogTitle
- ogDescription
- imageAlt
- structuredData
- translationStatus
- sourceContentHash
- sourceUpdatedAt
- translatedAt
- reviewedAt
- reviewedBy
- publishedAt
- unpublishedAt
- createdAt
- updatedAt
```

Contraintes :

```text
UNIQUE(site_page_id, locale)
UNIQUE(locale, slug)
```

Statuts minimum :

```text
missing
draft
ai_translated
to_review
reviewed
published
outdated
```

Appliquer le meme patron a :

- `PracticeTranslation`
- `ServiceTranslation`
- `ProjectTranslation`
- `TeamMemberTranslation`
- `LegalPageTranslation`
- `ContentItemTranslation`
- `HomeSectionTranslation`
- `HomeCardTranslation`
- si besoin : `FaqTranslation`, `MediaTranslation`, `CtaTranslation`, `GlossaryTermTranslation`

## Slugs et redirections

Ajouter une table generique :

```text
LocalizedSlugHistory
- id
- resourceType
- resourceId
- locale
- oldSlug
- newSlug
- changedAt
- changedBy
```

Regles :

- collision interdite par `locale + slug` ;
- modification d'un slug publie = creation automatique d'un historique ;
- ancienne URL = redirection 301 vers la nouvelle URL localisee ;
- aucune redirection EN/ES vers FR pour masquer une traduction absente.

## Resolution publique

Introduire un resolver commun :

```text
LocalizedContentResolver
- resolve(locale, slug, contentType?)
- generateUrl(entity, locale)
- getPublishedAlternates(entity)
```

Comportement :

- FR : routes sans prefixe ;
- EN/ES : routes prefixees ;
- traduction absente/non publiee : 404 ou 410 selon cas, pas de fallback FR visible ;
- language switcher : lien vers la traduction equivalente publiee, sinon option masquee/desactivee.

## SEO international

Chaque traduction publiee a :

- canonical propre ;
- `hreflang` reciproques uniquement vers traductions publiees, indexables et HTTP 200 ;
- `x-default` recommande vers la version FR canonique ou une page de choix langue si elle existe plus tard ;
- sitemap uniquement avec traductions publiees, indexables, canonicales.

Architecture sitemap recommandee :

```text
/sitemap.xml
/sitemap-fr.xml
/sitemap-en.xml
/sitemap-es.xml
```

## Back-office

Ajouter une vue transversale de couverture :

```text
Contenu | FR | EN | ES | Derniere maj source | Actions
```

Indicateurs :

- traduction manquante ;
- IA non relue ;
- publiee ;
- obsolete ;
- valideur ;
- date de traduction ;
- preview locale ;
- publication/depublication par locale.

Les formulaires d'edition doivent etre organises par onglets FR / EN / ES.

## Obsolescence

Pour chaque traduction non FR :

- calculer `sourceContentHash` depuis les champs FR significatifs ;
- comparer au hash stocke lors de la traduction ;
- si different : passer en `outdated` ou afficher un avertissement.

La publication d'une traduction IA doit rester impossible tant qu'elle n'est pas `reviewed`.

## IA et glossaire

Ajouter un glossaire :

```text
TranslationTerm
- id
- french
- englishPreferred
- spanishPreferred
- context
- forbiddenAlternatives
- notes
- status
```

Le prompt IA doit inclure :

- locale cible ;
- URL/slug attendu ;
- type de contenu ;
- intention SEO ;
- glossaire valide ;
- interdiction de publication automatique.

## Migration conseillee

1. Corriger le socle locale : `fr` par defaut, routes prefixees `en|es`.
2. Creer `SitePageTranslation` + resolver + tests.
3. Migrer `SitePage` FR existant vers `SitePageTranslation(fr)`.
4. Adapter canonical, hreflang, language switcher et sitemap.
5. Migrer `Practice` puis `Services`.
6. Migrer `LegalPage`, `Projet`, `Team`, homepage blocks.
7. Auditer et vider progressivement le contenu editorial en dur des Twig.
8. Ajouter dashboard BO de couverture et workflow IA.
9. Etendre aux formulaires, emails, Consultant IA et questionnaires ERP.

## Tests minimum

- FR conserve les URLs existantes.
- EN/ES resolvent avec prefixe et slug localise.
- absence de traduction publiee = pas de contenu FR visible.
- `locale + slug` unique.
- language switcher pointe vers la ressource equivalente.
- canonical et hreflang n'incluent que des traductions publiees.
- sitemap exclut drafts, missing, `to_review`, `outdated`.
- traduction IA non revue impossible a publier.

