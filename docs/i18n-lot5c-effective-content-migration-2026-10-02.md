# I18N-5C - Effective Content Migration

Date: 2026-10-02

Base commit: `bf2f5308b6c4582133a86cefaf2c6e7da89c9e21`

## Result

LOT RESULT: PARTIAL

CORE WEBSITE RESULT: PARTIAL

This lot starts the effective runtime migration by moving SEO landing narratives out of `SeoLandingController` and reading them from `SitePageTranslation.structuredData.landingNarrative`.

## Done

- Removed the public SEO narrative array from `SeoLandingController`.
- Added `SitePagePublicView::getStructuredData()`.
- Switched SEO landing rendering to Doctrine-backed `structuredData`.
- Imported 11 existing SEO narratives into local FR `SitePageTranslation.structuredData`.
- Moved generic SEO landing microcopy fallbacks to `translations/messages.fr.yaml`.
- Kept ERP questionnaire and AI consultant isolated for dedicated lots.

## Not Done

- Homepage editorial blocks not migrated.
- PublicSiteConfig editorial strings not migrated.
- Team defaults not migrated.
- Most Twig editorial fallbacks outside the SEO DB landing template not migrated.
- JS chat microcopy not migrated.
- Runtime public content still exists in `PublicSiteConfig`, practice/service templates and AI/ERP modules.

## KPIs

- Qualified groups before: 37
- Public editorial hardcoded before: at least 27 groups
- Public editorial hardcoded after: at least 26 groups
- Core website public editorial hardcoded after: at least 24 groups
- Public microcopy hardcoded before: at least 4 groups
- Public microcopy hardcoded after: at least 3 groups
- Moved to database: HC-001 partial/runtime controller array removed
- Moved to Symfony Translation: part of HC-004/HC-007 SEO generic fallbacks

## Area Status

- SEO narratives: PARTIAL, controller array removed; 11 local FR records populated.
- Landing page blocks: not migrated to `PageBlock` rows yet.
- CTA: generic SEO fallback text moved partly to Translation; contextual CTA still needs `PageCta`.
- FAQ: body parser unchanged; no `FaqItem` migration yet.
- Related links: runtime array removed with SEO narratives where imported; no `RelatedLink` rows yet.
- Homepage: not migrated.
- PublicSiteConfig: not migrated.
- Team defaults: not migrated.
- ERP questionnaire: DEFERRED.
- AI consultant: DEFERRED.

## Runtime Checks

Internal render smoke test:

- `/`: 200
- `/amoa-si`: 200
- `/erp-progiciel`: 200
- `/rgpd`: 200
- `/facturation-electronique-amoa`: 200
- `/ressources`: 200
- `/crm`: 200
- `/gmao`: 200
- `/cyber-securite`: 200

## Locale

- FR URLs: unchanged
- Default locale: unchanged
- Public EN routes: no
- Public ES routes: no
- EN content: 0
- ES content: 0

## Content Improvement

No editorial rewrite was performed.

## Next Recommended Lot

I18N-5D: make SEO narrative import reproducible through migration/command, then migrate homepage cards, PublicSiteConfig editorial content and Team defaults into the structured models.
