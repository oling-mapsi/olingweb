# I18N-6 routing foundation - 2026-10-02

## Rules

- FR remains the source locale and keeps historical URLs without `/fr`.
- EN and ES use prefixed URLs: `/en/<localized-slug>` and `/es/<localized-slug>`.
- URL is authoritative for locale. Browser language does not redirect.
- Public localized pages require `SitePageTranslation.translationStatus = published` and `unpublishedAt = null`.
- No public EN/ES fallback to FR.
- Published but outdated translations remain routable; outdated is an editorial alert, not a routing status.

## Implemented Foundation

- Symfony `default_locale` is now `fr` with `fr` translator fallback.
- `LocaleRouteContext` centralizes supported/reserved locales and URL prefix logic.
- `LocalizedUrlGenerator` builds canonical paths and hreflang alternates only for published translations.
- `LocalizedContentResolver` resolves public `SitePage` by `locale + localized slug`.
- Public EN/ES route: `/{_locale}/{slug}` with `_locale = en|es`.
- Root prefixes `fr`, `en`, `es` are reserved away from legacy catch-all routes.
- Sitemap foundation adds only published EN/ES `SitePageTranslation` URLs.

## Current Content State

- FR: existing public URLs unchanged.
- EN: no mass content generated.
- ES: no mass content generated.
- `/en` and `/es`: no route until localized home content exists.
- `/fr/...`: no canonical route.
