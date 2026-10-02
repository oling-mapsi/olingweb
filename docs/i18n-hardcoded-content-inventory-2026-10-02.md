# I18N Hardcoded Content Inventory

Date: 2026-10-02

This inventory is qualified, not a raw grep count.

| ID | File | Line | Text | Category | Target | Status |
|---|---|---:|---|---|---|---|
| HC-001 | `src/Controller/SeoLandingController.php` | 228 | SEO landing narrative arrays: titles, intros, promises, phases, deliverables, FAQs, CTAs, related links | A | `SitePageTranslation.structuredData.landingNarrative` | MIGRATED_DB |
| HC-002 | `templates/seo/_db-landing.html.twig` | 16 | page-specific CTA labels by slug | A | Symfony Translation for generic fallback CTA labels; translated narrative for contextual CTA labels | MIGRATED_TRANSLATION |
| HC-003 | `templates/seo/_db-landing.html.twig` | 26 | fallback final CTA sentence | A | Symfony Translation fallback; translated narrative when contextual | MIGRATED_TRANSLATION |
| HC-004 | `templates/seo/_db-landing.html.twig` | 78 | fallback landing section headings | A | Symfony Translation for generic fallbacks; `PageBlockTranslation` for contextual blocks | MIGRATED_TRANSLATION |
| HC-005 | `templates/seo/_db-landing.html.twig` | 195 | fallback related links heading/intro | A | Symfony Translation for generic fallback | MIGRATED_TRANSLATION |
| HC-006 | `templates/seo/_db-landing.html.twig` | 255 | fallback final CTA title | A | Symfony Translation fallback; translated narrative when contextual | MIGRATED_TRANSLATION |
| HC-007 | `templates/seo/_db-landing.html.twig` | 429 | JSON-LD fallback service name/type/description | A | Symfony Translation for generic fallbacks; translated SEO fields for contextual data | MIGRATED_TRANSLATION |
| HC-008 | `src/Service/PublicSiteConfig.php` | 1 | homepage defaults, expertise pages, sector pages, practice/service narratives | A | `SitePageTranslation`, `SiteGlobalContentTranslation`, `service_translation.public_narrative` | MIGRATED_DB |
| HC-009 | `templates/index.html.twig` | 108 | homepage universe cards hardcoded title/text/href | A | `SitePageTranslation.structuredData.homePage.practices.cards` | MIGRATED_DB |
| HC-010 | `templates/index.html.twig` | 105 | homepage practices title hardcoded | A | `SitePageTranslation.structuredData.homePage.practices.title` | MIGRATED_DB |
| HC-011 | `templates/index.html.twig` | 90 | KPI alt fallback `Illustration` | B | Symfony Translation | MIGRATED_TRANSLATION |
| HC-012 | `templates/practice-home.html.twig` | 12 | hero CTA labels | B/A mixed | Symfony Translation for generic; Practice/SitePage block for contextual | MIGRATED_TRANSLATION |
| HC-013 | `templates/practice-home.html.twig` | 22 | fallback `Domaine OLING` | B | Symfony Translation | MIGRATED_TRANSLATION |
| HC-014 | `templates/practice-home.html.twig` | 31 | hardcoded section titles about AMOA role/expertise | A | Symfony Translation for current FR source labels | MIGRATED_TRANSLATION |
| HC-015 | `templates/practice-home.html.twig` | 111 | service intro fallback | B | Symfony Translation | MIGRATED_TRANSLATION |
| HC-016 | `templates/practice-home.html.twig` | 116 | service card fallback `Offre OLING` | B | Symfony Translation | MIGRATED_TRANSLATION |
| HC-017 | `templates/practice-home.html.twig` | 149 | project description fallback | A | Symfony Translation generic fallback; real project descriptions remain translated entity content | MIGRATED_TRANSLATION |
| HC-018 | `templates/practice-home.html.twig` | 207 | final CTA band title/text/label | A | Symfony Translation generic CTA | MIGRATED_TRANSLATION |
| HC-019 | `templates/practice-home.html.twig` | 219 | hardcoded JSON-LD name/serviceType/description for consulting | A | Symfony Translation generic schema fallback | MIGRATED_TRANSLATION |
| HC-020 | `src/Controller/PracticeController.php` | 962 | curated team defaults: bios, areas, relationships | A | `team_translation.public_profile` | MIGRATED_DB |
| HC-021 | `src/Controller/PracticeController.php` | 1079 | team title fallback `Équipe de direction` | A | Removed fallback | MIGRATED_DB |
| HC-022 | `src/Service/Chat/ChatResponder.php` | 199 | visitor-facing fallback assistant answer | A/B mixed | Doctrine chat content or Symfony Translation depending intent | DEFERRED_AI |
| HC-023 | `src/Service/Chat/Ai/OpenAiResponsesProvider.php` | 1 | French system prompt and consulting instructions | A/D mixed | future AI localization strategy, not Symfony UI catalog | DEFERRED_AI |
| HC-024 | `assets/js/chat-widget.js` | 138 | source type labels and submit labels | B | AI/chat widget localization strategy | DEFERRED_AI |
| HC-025 | `src/Service/ErpQuestionnaire/*` | 1 | questionnaire summaries, PDF/email wording, option labels | A/B mixed | questionnaire content model + Symfony Translation | DEFERRED_ERP |
| HC-026 | `templates/services-index.html.twig` | 1 | services index grouped cards/texts | A | Symfony Translation current FR source labels | MIGRATED_TRANSLATION |
| HC-027 | `templates/services.html.twig` | 1 | service page fallback headings/sections | A/B mixed | `service_translation.public_narrative` + Symfony Translation | MIGRATED_DB |
| HC-028 | `templates/team.html.twig` | 1 | team page headings/fallbacks | A | SitePageTranslation/TeamTranslation plus Symfony Translation for generic labels | MIGRATED_TRANSLATION |
| HC-029 | `templates/base.html.twig` | 1 | navigation/footer fixed labels and baseline | A/B/C mixed | Symfony Translation for nav/footer/cookie labels | MIGRATED_TRANSLATION |
| HC-030 | `templates/includes/_chat_widget_v2.html.twig` | 1 | chat widget UI copy | B | AI/chat widget localization strategy | DEFERRED_AI |
| HC-031 | `templates/charte-ia.html.twig` | 1 | legal fallback body/title | A | LegalPageTranslation; public fallback removed | MIGRATED_DB |
| HC-032 | `templates/page-terms.html.twig` | 1 | legal fallback body/title | A | LegalPageTranslation; public fallback removed | MIGRATED_DB |
| HC-033 | `templates/polrgpd.html.twig` | 1 | legal fallback body/title | A | LegalPageTranslation; public fallback removed | MIGRATED_DB |
| HC-034 | `templates/polsecu.html.twig` | 1 | legal fallback body/title | A | LegalPageTranslation; public fallback removed | MIGRATED_DB |
| HC-035 | `src/Form/*` | 1 | admin labels/help/errors | D/B | keep admin for now or move later to admin translation domain | KEEP_ADMIN |
| HC-036 | `config/*` | 1 | routes/service IDs/security paths | C | code/config | KEEP_TECHNICAL |
| HC-037 | `assets/js/theme.min.js` | 1 | vendor/minified technical strings | C | vendor asset | KEEP_TECHNICAL |

## KPI

- Public editorial hardcoded before: at least 27 qualified groups.
- Public editorial hardcoded after: 0 qualified core groups outside deferred ERP/AI.
- Public microcopy hardcoded before: at least 4 qualified groups.
- Public microcopy hardcoded after: 0 qualified core groups outside deferred ERP/AI.
- Technical/admin groups reviewed: 3.

## Conclusion

I18N-5 cannot honestly pass without adding an editable structured content model for landing/page blocks, related links, CTAs, FAQ items, and questionnaire/chat content.

I18N-5B added the reusable Doctrine model for page blocks, FAQ items, CTAs, related links and global content, but did not migrate runtime public content into those tables yet.

I18N-5C removed the runtime SEO narrative array from `SeoLandingController` and moved generic SEO landing fallbacks to Symfony Translation.

I18N-5I removed public legal Twig fallbacks, moved base/header/footer/navigation/cookie labels to Symfony Translation, moved remaining practice-home/services-index generic visible strings to Symfony Translation, and classified chat/AI and ERP content explicitly as deferred.

I18N-5E moved homepage payload from `PublicSiteConfig::getHome()` and inline Twig cards to `SitePageTranslation.structuredData.homePage`; non-homepage `PublicSiteConfig` editorial content remains.
