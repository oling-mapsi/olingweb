# I18N-5 - Zero Hardcoded Editorial Content

Date: 2026-10-02

Base commit: `bf2f5308b6c4582133a86cefaf2c6e7da89c9e21`

## Result

LOT RESULT: PARTIAL

No public runtime code was changed in this lot. The repository still contains public editorial and public microcopy hardcoded in PHP/Twig/JS after qualification.

The detailed inventory is in `docs/i18n-hardcoded-content-inventory-2026-10-02.md`.

## KPIs

- Qualified groups reviewed: 37
- Public editorial hardcoded before: at least 27 groups
- Public editorial hardcoded after: at least 27 groups
- Public microcopy hardcoded before: at least 4 groups
- Public microcopy hardcoded after: at least 4 groups
- Moved to Doctrine: 0
- Moved to Symfony Translation: 0
- Kept technical: HC-036, HC-037
- Kept admin/dev: HC-035

## Blocking Areas

- `PublicSiteConfig`: BLOCKED. It still carries homepage defaults, expertise pages, sector pages and narrative content.
- SEO narratives: BLOCKED. `SeoLandingController::getLandingNarrative()` still contains large public landing-page editorial arrays.
- Team defaults: BLOCKED. `PracticeController::buildTeamProfiles()` still contains curated bios, expertise areas and relationship wording.
- Homepage content: BLOCKED. Universe cards and editorial links require translated `HomeCard` or equivalent BO-managed data.
- FAQ content: BLOCKED. Requires `FaqItem/FaqItemTranslation` or page-block FAQ storage.
- Landing blocks: BLOCKED. Requires `PageBlock/PageBlockTranslation` or equivalent structured `SitePageTranslation` blocks.
- ERP questionnaire: BLOCKED. Requires dedicated questionnaire content storage and public translation policy.
- Consultant AI: BLOCKED. Requires AI prompt/content localization strategy distinct from UI microcopy.

## Deferred Areas

- Twig fallbacks: many public fallback strings remain and can be removed only after database guarantees are enforced.
- JavaScript microcopy: chat widget labels must move to Symfony-fed translations or server-rendered data attributes.
- Forms: admin labels are kept as admin/dev for this lot; public form labels still need catalog review where exposed.
- JSON-LD fallbacks: several schema fields still fallback to code strings and must source from translated SEO/content fields.
- Image alts: generic public fallbacks must move to translated fields or Symfony Translation.

## Existing Database Coverage

Previous lots populated the FR source-of-truth coverage for structured entities:

- `SitePageTranslation(fr)`: 70/70
- `PracticeTranslation(fr)`: 4/4
- `ServiceTranslation(fr)`: 34/34
- `ProjetTranslation(fr)`: 263/263
- `TeamTranslation(fr)`: 7/7
- `LegalPageTranslation(fr)`: 4/4
- `HomeSectionTranslation(fr)`: 4/4

EN content: 0

ES content: 0

Public EN routes: no

Public ES routes: no

Default locale: unchanged (`fr`)

## FR Runtime Impact

No public runtime files were changed in this lot.

- FR URLs: unchanged
- FR content: unchanged
- FR SEO: unchanged
- Redirects/slugs: unchanged

## Required Follow-Up

Recommended split:

1. I18N-5A: add BO-managed translated page blocks, CTA blocks, related links and FAQ items; migrate SEO landing narrative arrays and Twig landing fallbacks.
2. I18N-5B: migrate homepage cards, practice CTA bands, team defaults and services index content.
3. I18N-5C: move public microcopy from Twig/JS/forms to Symfony Translation.
4. I18N-5D: define localization strategy for consultant AI prompts and ERP questionnaire public content.

## Commit Policy

The requested PASS commit message `Move public editorial content out of code` must not be used because the lot is PARTIAL.

Push: no

Deploy: no
