# OLING — Public institutional pages / leadership lot 2
Date: 2026-10-13
Branch: `design/public-secondary-foundation-2026-10-13`
Production: NOT TOUCHED
PR: https://github.com/oling-mapsi/olingweb/pull/1 (draft)

## Intent
Continue the public secondary-page redesign following the shared full-width header/hero foundation.

- `/a-propos`, `/en/about`, `/es/quienes-somos`: editorial positioning panels and two-person leadership focus.
- `/a-propos/team`, `/en/team`, `/es/equipo`: two substantial leadership profiles rather than a directory of seven.
- Profiles selected by **exact, public slugs** `florestan-rouet` and `dorothee-maitrias` in `PublicLeadershipSelector`.
- Selection runs **after** locale-specific public/published DB retrieval and profile resolution.
- All other published Team entities, their translations, services and project relationships remain unchanged. No migration and no BO changes.
- `Person` JSON-LD on Team now describes exactly the two visible profiles, reusing existing `@id`, job titles and relationships; no invisible SEO-only team records.
- FR/EN/ES leadership UI copy is stored in `translations/messages.*.yaml`. Other CMS content is retained, but leadership section heading/introduction and team hero H1/intro are intentionally overridden for editorial consistency.

## SEO impact: deliberate exception — not neutral
The visual foundation changes no routes, canonicals, hreflang, robot rules, title or meta-description tags, sitemap generation or link destinations. The editorial lot **does** intentionally change:
1. Team-page H1 and introduction for FR/EN/ES.
2. About-page team section heading, introduction and CTA labels for FR/EN/ES.
3. Team-page visible profiles from seven to two and therefore `Person` JSON-LD entities from seven to two.
4. Removal of five old deep anchors on the Team page. Existing service/practice references need checking for links to these anchors; repository code search found only schema-generated anchors, not explicit outbound links.

These are content-level SEO changes; monitor search index impressions/queries and incoming profile anchors after release. Do **not** claim zero SEO impact or retain hidden old profiles for crawlers.

## Real portrait quality
The published asset paths were verified in GitHub:
- `public/img/people/florestan-oling.png`: 180×180px.
- `public/img/people/dorothee-oling.jpg`: 180×180px.

The new design therefore displays sharp square photographs at **no more than their native 180px**, within a generous graphic media column. High-resolution, approved originals are required if full-height photographic portraits are desired; **do not AI-create or silently upscale faces**.

## Changed files
- Backend: `src/Service/PublicLeadershipSelector.php`, `src/Controller/PracticeController.php`.
- Templates: `templates/about.html.twig`, `templates/team.html.twig`, `templates/public/_leadership_card.html.twig`.
- Styling: `assets/styles/public-leadership.css`, `assets/styles/public-secondary.css`, import in `assets/app.js`.
- Languages: `translations/messages.fr.yaml`, `messages.en.yaml`, `messages.es.yaml`.
- Tests: `tests/PublicLeadershipSelectorTest.php`, `tests/TeamProfilesSourceTest.php`, `tests/HomePageSourceTest.php`.
- Real browser audit: `scripts/audit-public-visual-theme.mjs`, `npm run audit:public-visual`.

## Static verification completed via GitHub connector
- Source branch compare to main: isolated, ahead, not merged; no concurrent main drift as of comparison.
- All referenced modified files resolvable on branch.
- Template `if/endif`, `for/endfor`, `block/endblock` nesting: PASS static checks.
- CSS brace counts: PASS static checks.
- Translations: `leadership` keys exist in FR/EN/ES.
- Front-end imports and CLI command: present and parse as package JSON.
- Site head meta/canonical blocks still present in templates and base template untouched.
- Source real portraits are 180×180.
- No CI statuses or workflow runs found on PR HEAD. **No actual build, PHPUnit, browser screenshot or SEO diff has yet run.**

## Required local / staging release gates
1. Build/test on the PR branch: `npm ci && npm run build`, `php bin/phpunit --filter 'PublicLeadershipSelectorTest|TeamProfilesSourceTest|HomePageSourceTest|TeamRuntimeArchitectureTest'`, Symfony cache warmup, Twig lint and route checks.
2. Browser-smoke against the PR branch served locally or staging: `OLING_AUDIT_BASE_URL=http://127.0.0.1:8000 npm run audit:public-visual`. Install Playwright Chromium beforehand if needed.
3. Capture before/after screenshots at 360,390,768,1024,1366,1440,1920px and inspect for menus, cropped content, typography and visual fit.
4. For SEO baseline comparison, run the new script in baseline-capture mode against an actual **main-branch running instance** (copy the script to the main worktree as needed) with `OLING_AUDIT_CAPTURE_BASELINE=1`. Use its JSON in `OLING_AUDIT_BASELINE_FILE=...` when auditing the PR branch. Compare metadata; review intentional team H1/structured content diffs separately.
5. Verify real images loaded (native 180px), JSON-LD Person names match visible cards, all two leadership records published for FR/EN/ES.
6. Verify header/mobile navigation, chat, cookies, language switcher, forms, no horizontal overflow, and Web Vitals.
7. Approve screenshot review before changing PR to ready and merging. **No production deployment** in this phase.

## Open decisions
- Larger executive portraits require approved higher-resolution source files (not present in this repository).
- Editorial team-section copy remains stored in legacy CMS as well as the new visible UI translation keys; decide whether to expose leadership-specific fields in the BO in a later dedicated migration, rather than changing the BO silently now.
