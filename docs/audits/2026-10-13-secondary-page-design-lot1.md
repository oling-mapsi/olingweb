# Public secondary pages — design lot 1 (13 October 2026)

## Scope and isolation
- Working branch: `design/public-secondary-foundation-2026-10-13`; target branch: `main`.
- New CSS: `assets/styles/public-secondary.css`, imported after `assets/styles/app.css` from `assets/app.js`.
- Scope guard: `body.oling-body:not(.oling-home):not(.admin)`.
- No route, content, Twig SEO metadata, crawler policy, structured data, or PHP changes.
- No changes to public homepage styling by design; no changes to back-office styling.
- No changes to team roster: management-only editorial change is a separate, SEO-sensitive lot.

## Visual intent
- Light, full-viewport navigation backdrop, not a rounded floating topbar.
- Full-bleed secondary-page hero; text retains its existing Bootstrap `.container` width.
- Editorial, accessible dark-on-light headline and introductory text.
- Existing primary/secondary CTA and highlight HTML preserved.
- Mobile collapse behavior stays driven by existing JS/Bootstrap.

## Required local checks — NOT YET EXECUTED
The GitHub connector can write the isolated branch but this execution environment cannot fetch the repository into the local browser runtime (DNS disabled). Do not treat this branch as browser-tested.

1. On a local clone, checkout the branch; run dependency install if needed, `npm run build` (or the project's production build task), and existing PHP/Twig/JS tests.
2. Use a real browser/Playwright at 360, 390, 768, 1024, 1366, 1440, and 1920px. Test /, /a-propos, /a-propos/team, /expertises, /services, /secteurs, /projets, /ressources, /contact plus EN/ES equivalents.
3. At each viewport, record computed `background`, `borderRadius`, bounds and `overflow-x` for `#header`, `#header .oling-topbar`, `.oling-shell-hero`, and `.oling-shell-hero__inner`; add screenshots, look for collisions/clipping.
4. Assert homepage computed styles and snapshots unchanged; assert no admin changes.
5. Verify desktop submenu and mobile menu interactions, language dropdown, keyboard navigation, hover/focus contrasts and fixed/sticky header scroll behavior.
6. Compare before/after HTML for `title`, description, canonical, hreflang, robots, OpenGraph, JSON-LD, H1 text, in-page links, and sitemap URLs. No text or SEO changes are authorized in this lot.
7. Run Lighthouse/Web Vitals, compare LCP, CLS, accessibility. Do not merge on regressions.

## Release policy
Do NOT merge, push deployment artifacts to production, or release until real build, browser QA, responsive screenshot review and SEO diff pass. If QA finds an issue, correct CSS in the same branch before approval.
