# I18N-5B - Structured Editorial Content

Date: 2026-10-02

Base commit: `bf2f5308b6c4582133a86cefaf2c6e7da89c9e21`

## Result

LOT RESULT: PARTIAL

CORE WEBSITE RESULT: PARTIAL

This lot creates the reusable Doctrine model required to move public editorial content out of runtime code. It does not yet migrate the existing public strings from controllers/Twig/JS into those tables.

## Models

- PageBlock model: created
- PageBlockTranslation FR source-of-truth: created
- FAQ model: created
- FaqItemTranslation FR source-of-truth: created
- CTA model: created
- PageCtaTranslation FR source-of-truth: created
- RelatedLink model: created
- RelatedLinkTranslation FR source-of-truth: created
- Global content model: created
- SiteGlobalContentTranslation FR source-of-truth: created

All new root entities keep technical data only. Editorial text lives in translation entities.

## New Tables

- `page_block`
- `page_block_translation`
- `faq_item`
- `faq_item_translation`
- `page_cta`
- `page_cta_translation`
- `related_link`
- `related_link_translation`
- `site_global_content`
- `site_global_content_translation`

## Migration

Migration: `Version20261002170000`

Local migration status: applied successfully.

## Hardcoded Content KPIs

- Qualified groups before: 37
- Public editorial hardcoded before: at least 27 groups
- Public editorial hardcoded after: at least 27 groups
- Core website public editorial hardcoded after: at least 25 groups
- Public microcopy hardcoded before: at least 4 groups
- Public microcopy hardcoded after: at least 4 groups
- Moved to database: 0 runtime content groups
- Moved to Symfony Translation: 0 microcopy groups

## Area Status

- PublicSiteConfig: model available, content not migrated
- SEO narratives: model available, content not migrated
- Team defaults: not migrated
- Twig fallbacks: not migrated
- Homepage: not migrated
- Landing pages: model available, content not migrated
- ERP questionnaire: DEFERRED, needs dedicated lot
- AI consultant: DEFERRED, needs dedicated prompt/content model

## Database Coverage

Existing FR coverage remains from previous lots:

- SitePage: 70/70 FR
- Practice: 4/4 FR
- Service: 34/34 FR
- Projet: 263/263 FR
- Team: 7/7 FR
- LegalPage: 4/4 FR
- HomeSection: 4/4 FR

New model counts after migration:

- PageBlock: 0
- FaqItem: 0
- PageCta: 0
- RelatedLink: 0
- SiteGlobalContent: 0

EN content: 0

ES content: 0

Public EN routes: no

Public ES routes: no

Default locale: unchanged (`fr`)

## Runtime Impact

No public rendering path was switched to the new tables in this lot.

- FR content: unchanged
- FR SEO: unchanged
- FR URLs: unchanged
- `/fr`: no
- `/en`: no
- `/es`: no

## Content Improvement

No editorial rewrite was performed.

## Next Recommended Lot

I18N-5C: migrate SEO landing narratives, CTA blocks, FAQ, related links and homepage cards into the new tables, then remove the runtime hardcoded arrays and Twig fallbacks.
