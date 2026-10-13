/**
 * Real browser audit. No hard-coded pass/fail counters.
 *
 * OLING_AUDIT_BASE_URL=http://127.0.0.1:8000 npm run audit:public-visual
 * OLING_AUDIT_CAPTURE_BASELINE=1 ...   # Capture old main before styling changes
 * OLING_AUDIT_BASELINE_FILE=path.json ... # Compare metadata against that baseline
 */
import { chromium } from '@playwright/test';
import { existsSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { join, resolve } from 'node:path';

const baseUrl = process.env.OLING_AUDIT_BASE_URL || 'http://127.0.0.1:8000';
const out = resolve(process.env.OLING_AUDIT_OUTPUT_DIR || 'docs/redesign');
const baselinePath = process.env.OLING_AUDIT_BASELINE_FILE;
const captureBaseline = process.env.OLING_AUDIT_CAPTURE_BASELINE === '1';
const baseline = baselinePath ? JSON.parse(readFileSync(resolve(baselinePath), 'utf8')) : null;
const routes = [
  ['/', 'home'], ['/a-propos', 'about'], ['/a-propos/team', 'team'],
  ['/en/about', 'about'], ['/en/team', 'team'],
  ['/es/quienes-somos', 'about'], ['/es/equipo', 'team'],
  ['/expertises', 'secondary'], ['/services', 'secondary'],
  ['/secteurs', 'secondary'], ['/projets', 'secondary'],
  ['/ressources', 'secondary'], ['/contact', 'secondary'],
];
const requestedRoutes = process.env.OLING_AUDIT_ROUTES?.split(',').map((value) => value.trim()).filter(Boolean);
if (requestedRoutes?.length) {
  const supported = new Set(routes.map(([route]) => route));
  for (const route of requestedRoutes) {
    if (!supported.has(route)) throw new Error(`Unsupported preview route ${route}`);
  }
  routes.splice(0, routes.length, ...routes.filter(([route]) => requestedRoutes.includes(route)));
}
const requestedWidths = process.env.OLING_AUDIT_WIDTHS?.split(',').map((value) => Number.parseInt(value.trim(), 10)).filter(Number.isFinite);
const widths = requestedWidths?.length ? requestedWidths : [360, 390, 768, 1024, 1366, 1440, 1920];
const fixtureLabel = process.env.OLING_AUDIT_FIXTURE_LABEL?.trim() || '';
const skipScreenshots = process.env.OLING_AUDIT_SKIP_SCREENSHOTS === '1';
const failures = [], records = [];
const seoKeys = ['title', 'description', 'canonical', 'robots', 'hreflang', 'ogTitle', 'ogDescription'];
// An isolated comparison runs Symfony main and the PR on different localhost
// ports. Compare paths, not local ephemeral ports; retain real public hosts.
const comparableLocalUrl = (value) => {
  if (!value) return value;
  try {
    const url = new URL(value);
    if (['127.0.0.1', 'localhost', '[::1]'].includes(url.hostname)) {
      return url.pathname + url.search + url.hash;
    }
  } catch { /* preserve non-URL values */ }
  return value;
};
const comparableSeo = (seo) => ({
  ...seo,
  canonical: comparableLocalUrl(seo.canonical),
  hreflang: Object.fromEntries(
    Object.entries(seo.hreflang || {}).map(([language, url]) => [language, comparableLocalUrl(url)])
  ),
});
mkdirSync(out, { recursive: true });
mkdirSync(join(out, 'screenshots-public-design'), { recursive: true });

const browser = await chromium.launch();
try {
  for (const width of widths) {
    const context = await browser.newContext({ viewport: { width, height: width < 800 ? 900 : 900 }, reducedMotion: 'reduce' });
    const page = await context.newPage();
    for (const [route, kind] of routes) {
      const issue = (message) => failures.push(`${route} [${width}px]: ${message}`);
      let status = 0;
      try {
        const response = await page.goto(new URL(route, baseUrl).href, { waitUntil: 'domcontentloaded', timeout: 45000 });
        status = response?.status() || 0;
        if (status !== 200) {
          issue(`HTTP ${status}`);
          records.push({ route, width, status });
          continue;
        }
        // Symfony's development profiler may still be present in the fixture.
        // Keep it outside interactions; this is test harness CSS, not a site edit.
        await page.addStyleTag({ content: '[id^="sfwdt"], .sf-toolbar { display:none!important; pointer-events:none!important; }' });
        // Consent is refused through the real public control in this temporary
        // browser context. This clears the banner from design screenshots while
        // verifying that the refusal UI is functional.
        const refuseCookies = page.locator('#cookie-banner .js-cookie-refuse');
        if (await refuseCookies.isVisible()) {
          await refuseCookies.click();
          await page.waitForTimeout(180);
          if (await page.locator('#cookie-banner').isVisible()) {
            issue('Cookie refusal did not hide the banner');
          }
        }
        const data = await page.evaluate(() => {
          const nodeInfo = (selector) => {
            const el = document.querySelector(selector);
            if (!el) return null;
            const rect = el.getBoundingClientRect(), style = getComputedStyle(el);
            return {
              x: rect.x, width: rect.width, borderRadius: style.borderTopLeftRadius,
              backgroundColor: style.backgroundColor, backgroundImage: style.backgroundImage, color: style.color,
            };
          };
          const meta = (name) => document.querySelector(name)?.getAttribute('content') || '';
          const switcher = document.querySelector('#languageSwitcher');
          const caret = switcher ? getComputedStyle(switcher, '::after') : null;
          const languageCaret = caret ? {
            width: Number.parseFloat(caret.width),
            height: Number.parseFloat(caret.height),
            display: caret.display,
            backgroundColor: caret.backgroundColor,
            backgroundImage: caret.backgroundImage,
            opacity: caret.opacity,
            content: caret.content,
          } : null;
          const schemaNames = [];
          for (const element of document.querySelectorAll('script[type="application/ld+json"]')) {
            try {
              const root = JSON.parse(element.textContent || '{}');
              for (const item of Array.isArray(root['@graph']) ? root['@graph'] : [root]) {
                if (item?.['@type'] === 'Person' && String(item['@id'] || '').includes('/a-propos/team#')) {
                  schemaNames.push(item.name);
                }
              }
            } catch { /* invalid JSON-LD is not silently counted as Person */ }
          }
          return {
            title: document.title,
            description: meta('meta[name="description"]'),
            canonical: document.querySelector('link[rel="canonical"]')?.href || '',
            robots: meta('meta[name="robots"]'),
            hreflang: Object.fromEntries(
              [...document.querySelectorAll('link[rel="alternate"][hreflang]')]
                .map((el) => [el.hreflang, el.href]).sort(([a], [b]) => a.localeCompare(b))
            ),
            ogTitle: meta('meta[property="og:title"]'),
            ogDescription: meta('meta[property="og:description"]'),
            h1: [...document.querySelectorAll('main h1')].map((el) => el.textContent.trim()),
            isHome: document.body.classList.contains('oling-home'),
            isEditorial: Boolean(document.querySelector('main.oling-public-editorial')),
            languageCaret,
            overflow: document.documentElement.scrollWidth - window.innerWidth,
            header: nodeInfo('#header'),
            topbar: nodeInfo('#header .oling-topbar'),
            hero: nodeInfo('main section.oling-shell-hero'),
            heroPanel: nodeInfo('.oling-shell-hero__inner'),
            languageToggle: nodeInfo('#header .oling-language-switcher__toggle'),
            languageArrow: (() => {
              const node = document.querySelector('#header .oling-language-switcher__toggle');
              if (!node) return null;
              const css = getComputedStyle(node, '::after');
              return {
                content: css.content,
                display: css.display,
                rightBorder: css.borderRightColor,
                rightWidth: css.borderRightWidth,
                bottomWidth: css.borderBottomWidth,
                background: getComputedStyle(node).backgroundColor,
              };
            })(),
            aboutCards: document.querySelectorAll('.oling-leadership-grid--preview .oling-leadership-card').length,
            teamNames: [...document.querySelectorAll('.oling-leadership-grid--detail .oling-leadership-card__name')].map((el) => el.textContent.trim()),
            personNames: schemaNames,
            editorialGeometry: (() => {
              const rect = (sel) => {
                const node = document.querySelector(sel);
                if (!node) return null;
                const b = node.getBoundingClientRect();
                return { x: b.x, y: b.y, width: b.width, height: b.height, centerOffset: (b.x + b.width / 2) - (innerWidth / 2) };
              };
              const featured = document.querySelector('.oling-universe--1');
              const resource = document.querySelector('.oling-resource-story:nth-child(4n+1)');
              return {
                resourcesHeading: rect('.oling-resources-library__heading'),
                resourcesCuration: rect('.oling-resources-curation__content'),
                sectorsCard: rect('.oling-sectors-grid .oling-sector-card'),
                sectorsVisual: rect('.oling-sectors-grid .oling-sector-card__visual'),
                expertiseFeatured: rect('.oling-universe--1'),
                expertiseDomainCards: [1,2,3].map((number) => rect(`.oling-universe--${number}`)),
                expertiseBg: featured ? getComputedStyle(featured).backgroundImage : null,
                expertiseTextColor: featured ? getComputedStyle(featured.querySelector('.oling-universe__head h3')).color : null,
                resourceFeaturedBg: resource ? getComputedStyle(resource).backgroundImage : null,
                projectCount: document.querySelectorAll('.oling-proof-mission').length,
                sectorsCount: document.querySelectorAll('.oling-sectors-grid .oling-sector-card').length,
                expertiseCount: document.querySelectorAll('.oling-expertises-featured .oling-expertise-index-card').length,
              };
            })(),
          };
        });
        const seo = Object.fromEntries(seoKeys.map((key) => [key, data[key]]));
        const previous = baseline?.records.find((r) => r.route === route && r.width === width);
        if (baseline) {
          if (!previous?.seo) {
            issue('SEO baseline record missing');
          } else {
            for (const key of seoKeys) {
              if (JSON.stringify(comparableSeo(seo)[key]) !== JSON.stringify(comparableSeo(previous.seo)[key])) issue(`SEO ${key} changed`);
            }
          }
        }
        if (!data.canonical || !data.description) issue('Missing canonical or description');
        if (!data.languageArrow) {
          issue('Public language selector or chevron missing');
        } else if (!captureBaseline && (
          data.languageArrow.content === 'none' ||
          data.languageArrow.display === 'none' ||
          parseFloat(data.languageArrow.rightWidth) < 1.5 ||
          parseFloat(data.languageArrow.bottomWidth) < 1.5 ||
          data.languageArrow.rightBorder !== 'rgb(24, 53, 76)' ||
          !['rgb(238, 241, 245)', 'rgb(226, 233, 240)'].includes(data.languageArrow.background) ||
          data.languageToggle?.color !== 'rgb(24, 53, 76)'
        )) {
          issue('Language chevron/globe/text must be dark slate on an accessible pale-grey chip');
        }
        if (data.h1.length !== 1) issue(`Expected one H1; found ${data.h1.length}`);
        if (!captureBaseline && route === '/ressources') {
          if (!data.editorialGeometry.resourcesHeading || !data.editorialGeometry.resourcesCuration) {
            issue('Resource editorial sections missing');
          } else {
            if (Math.abs(data.editorialGeometry.resourcesHeading.centerOffset) > 14) issue('Resources article-library heading is not centered');
            if (Math.abs(data.editorialGeometry.resourcesCuration.centerOffset) > 14) issue('Resources curated expertises remain off-center');
          }
          if (data.editorialGeometry.resourceFeaturedBg && !data.editorialGeometry.resourceFeaturedBg.includes('gradient')) {
            issue('Featured resource card has no differentiated background');
          }
        }
        if (!captureBaseline && route === '/expertises' && data.editorialGeometry.expertiseFeatured) {
          if (!data.editorialGeometry.expertiseBg?.includes('gradient')) issue('Flagship expertise feature must have dark background');
          if (data.editorialGeometry.expertiseTextColor !== 'rgb(255, 255, 255)') issue('Flagship expertise dark feature heading must be white');
        }
        if (!captureBaseline && route === '/expertises') {
          const domainCards = data.editorialGeometry.expertiseDomainCards || [];
          if (domainCards.length !== 3 || domainCards.some((item) => !item)) {
            issue('All three expertise-domain cards must render');
          } else if (width >= 1200) {
            const [featured, second, third] = domainCards;
            if (Math.min(second.width, third.width) < featured.width * 0.42 ||
                Math.abs(second.width - third.width) > 24) {
              issue('Flagship expertise domains 02/03 are squeezed into an unbalanced grid');
            }
          } else if (width < 768 &&
                domainCards.some((card) => Math.abs(card.centerOffset) > 8)) {
            issue('Mobile expertise domain cards are not centered');
          }
        }
        if (!captureBaseline && route === '/secteurs' && data.editorialGeometry.sectorsCount) {
          const {sectorsCard, sectorsVisual} = data.editorialGeometry;
          if (sectorsCard && sectorsVisual && sectorsVisual.width < sectorsCard.width * 0.85) {
            issue('Sector visual cover shrank due to legacy card styles');
          }
        }
        if (data.overflow > 3) issue(`Horizontal overflow ${data.overflow}px`);
        if (!captureBaseline) {
          if (['/expertises', '/services', '/secteurs', '/projets', '/ressources', '/contact'].includes(route)
              && !data.isEditorial) {
            issue('Secondary page is missing editorial design scope');
          }
        }
        if (!captureBaseline) {
          if (kind === 'home' ? !data.isHome : data.isHome) issue('Wrong body home modifier');
          if (kind !== 'home') {
            if (!data.header || !data.topbar) issue('Header/topbar missing');
            else {
              if (Math.abs(data.header.x) > 2 || Math.abs(data.header.width - width) > 3) issue('Header not full viewport width');
              if (parseFloat(data.topbar.borderRadius) > 1) issue('Secondary navigation still rounded');
            }
            if (data.hero) {
              if (Math.abs(data.hero.x) > 2 || Math.abs(data.hero.width - width) > 3) issue('Hero not full width');
              if (parseFloat(data.heroPanel?.borderRadius || '0') > 1) issue('Hero still rounded');
            }
          }
          if (kind === 'team') {
            if (data.teamNames.length !== 2 || data.personNames.length !== 2) issue('Expected two leadership cards and two Person schema entries');
            if (data.teamNames.join('|') !== data.personNames.join('|')) issue('Person JSON-LD and visible leadership names differ');
          }
          if (kind === 'about' && data.aboutCards !== 2) issue('Expected two leadership previews');
          if (route === '/secteurs') {
            const sectorImages = page.locator('.oling-sector-card--photographic .oling-sector-card__visual img');
            const cards = await page.locator('.oling-sectors-grid .oling-sector-card').count();
            if (cards > 0 && await sectorImages.count() === 0) {
              issue('Sector cards do not show any real, versioned editorial images');
            }
            for (let i = 0, n = await sectorImages.count(); i < n; i++) {
              const photo = sectorImages.nth(i);
              await photo.scrollIntoViewIfNeeded();
              const valid = await photo.evaluate(async (img) => {
                await img.decode().catch(() => null);
                return img.naturalWidth >= 320;
              });
              if (!valid) issue(`Sector image ${i + 1} is broken or too small`);
            }
          }
          if (kind === 'about' || kind === 'team') {
            const photos = page.locator('.oling-leadership-card__media img');
            for (let i = 0, n = await photos.count(); i < n; i++) {
              const photo = photos.nth(i);
              await photo.scrollIntoViewIfNeeded();
              const valid = await photo.evaluate(async (img) => {
                await img.decode().catch(() => null);
                return img.naturalWidth > 0;
              });
              if (!valid) issue(`Portrait ${i + 1} failed to load`);
            }
          }
        }
        if (!skipScreenshots && (kind === 'about' || kind === 'team' || [390, 1366].includes(width))) {
          // Photographs are lazy-loaded lower in the document. Scrolling to
          // decode them can hide the sticky header; restore a true initial view.
          await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'instant' }));
          await page.waitForTimeout(300);
          const headerInViewport = async () => page.locator('#header').evaluate((header) => {
            const rect = header.getBoundingClientRect();
            return rect.top >= -4 && rect.bottom > 28 && getComputedStyle(header).visibility !== 'hidden';
          });
          if (!await headerInViewport()) {
            await page.reload({ waitUntil: 'domcontentloaded', timeout: 45000 });
            await page.waitForTimeout(300);
          }
          if (!await headerInViewport()) issue('Navigation header hidden at the top of the capture');
          if (await page.locator('#cookie-banner').isVisible()) issue('Cookie banner covers the design capture');
          await page.screenshot({
            path: join(out, 'screenshots-public-design', `${route === '/' ? 'home' : route.slice(1).replaceAll('/', '-')}-${width}.png`),
            fullPage: true,
            animations: 'disabled',
            // Remove ONLY the framework's QA/developer overlay. Real site UI,
            // layout, cookies behavior, and portraits remain browser-rendered.
            style: '[id^="sfwdt"], .sf-toolbar, .sf-toolbarreset { display: none !important; visibility: hidden !important; }',
          });
        }
        if (!skipScreenshots && width === 1366 && ['/a-propos', '/en/about', '/es/quienes-somos'].includes(route)) {
          const languageToggle = page.locator('#languageSwitcher');
          if (await languageToggle.isVisible()) {
            await languageToggle.screenshot({
              path: join(out, 'screenshots-public-design', `language-selector-${route.slice(1).replaceAll('/', '-')}-1366.png`),
              animations: 'disabled',
            });
          } else if (!captureBaseline) {
            issue('Desktop language selector not visible for close-up review');
          }
        }
        if (!captureBaseline && kind !== 'home' && width < 992) {
          const toggle = page.locator('#header .navbar-toggler').first();
          if (await toggle.isVisible()) {
            await toggle.click();
            await page.waitForTimeout(350);
            if (!await page.locator('#navbarNavMenuLeftAligned').evaluate((el) => el.classList.contains('show'))) {
              issue('Mobile menu did not expand');
            }
            const chatLauncher = page.locator('#oling-chat-widget .oling-chat-widget__launcher');
            if (await chatLauncher.count() && await chatLauncher.isVisible()) {
              issue('Chat launcher overlaps the expanded mobile navigation');
            }
            // Capture the real expanded mobile menu with the language switcher
            // visible; do not fabricate a CSS mock-up.
            if (!skipScreenshots && width === 390 && ['/a-propos', '/en/about', '/es/quienes-somos'].includes(route)) {
              const langInMenu = page.locator('#languageSwitcher');
              if (await langInMenu.isVisible()) {
                await langInMenu.screenshot({
                  path: join(out, 'screenshots-public-design', `language-selector-${route.slice(1).replaceAll('/', '-')}-390.png`),
                  animations: 'disabled',
                });
              } else {
                issue('Language selector hidden even after opening mobile navigation');
              }
            }
            // Close the genuine menu BEFORE interacting with the project
            // filters; an expanded mobile dropdown correctly intercepts
            // pointer events and otherwise makes this an artificial failure.
            await toggle.click();
            await page.waitForTimeout(300);
            if (await page.locator('#navbarNavMenuLeftAligned').evaluate((el) => el.classList.contains('show'))) {
              issue('Mobile menu did not collapse');
            }
            if (await chatLauncher.count() && !await chatLauncher.isVisible()) {
              issue('Chat launcher did not return after mobile navigation collapsed');
            }
          }
        }
        // Functional controls: never submit live data; just exercise safe UI.
        if (!captureBaseline) {
          if (route === '/contact') {
            for (const selector of ['#contact-form', '#contactWorkEmail', '#consent', '#button-send']) {
              if (!await page.locator(selector).count()) issue(`Missing contact control ${selector}`);
            }
          }
          if (route === '/projets') {
            const notes = page.locator('.oling-proof-quotes');
            if (await notes.count()) {
              const details = notes.first();
              if (await details.getAttribute('open') !== null) {
                issue('Factual project notes should be collapsed initially');
              }
              await details.locator('summary').click();
              if (await details.getAttribute('open') === null) issue('Factual project notes did not open');
              const fullQuote = details.locator('.oling-proof-quotes__body p');
              if (!await fullQuote.count() || !await fullQuote.first().isVisible()) {
                issue('Expanded project notes have no accessible factual evidence');
              }
              await details.locator('summary').click();
              if (await details.getAttribute('open') !== null) issue('Factual project notes did not close');
            }
            const filters = page.locator('[data-proof-filter]');
            if (await filters.count() > 1) {
              const button = filters.nth(1);
              await button.click();
              if (await button.getAttribute('aria-pressed') !== 'true') issue('Project filter did not select');
              await filters.first().click();
              if (await filters.first().getAttribute('aria-pressed') !== 'true') issue('Project filter did not reset');
            }
          }
          const languageToggle = page.locator('#languageSwitcher');
          if (await languageToggle.isVisible()) {
            await languageToggle.click();
            if (await languageToggle.getAttribute('aria-expanded') !== 'true'
                || !await page.locator('.oling-language-switcher__menu').isVisible()) {
              issue('Language selector dropdown did not open');
            }
            await languageToggle.press('Escape');
          }
        }
        records.push({ route, width, status, seo, h1: data.h1, layout: {
          header: data.header, topbar: data.topbar, hero: data.hero,
          heroPanel: data.heroPanel, languageToggle: data.languageToggle,
          languageArrow: data.languageArrow, horizontalOverflow: data.overflow,
        }, aboutCards: data.aboutCards, teamNames: data.teamNames, personNames: data.personNames,
          isEditorial: data.isEditorial, languageCaret: data.languageCaret, editorialGeometry: data.editorialGeometry });
      } catch (error) {
        issue(String(error?.message || error));
        records.push({ route, width, status, error: String(error) });
      }
    }
    await context.close();
  }
} finally {
  await browser.close();
}
const report = { timestamp: new Date().toISOString(), baseUrl, fixtureLabel, captureBaseline, baselinePath, skipScreenshots, records, failures };
writeFileSync(join(out, 'PUBLIC_THEME_AUDIT.json'), JSON.stringify(report, null, 2) + '\n');
writeFileSync(join(out, 'PUBLIC_THEME_AUDIT.md'), [
  '# OLING Public Theme — browser computed-style audit', '',
  `Base: ${baseUrl}`,
  `Data source: ${fixtureLabel || 'local/staging environment supplied by operator'}`,
  `Audit mode: ${captureBaseline ? 'baseline capture' : 'new design validation'}`,
  `Routes/viewport combinations: ${records.length}`,
  `Failures: ${failures.length}`, '',
  ...(failures.length ? failures.map((f) => `- ${f}`) : ['All sampled browser assertions passed.']), '',
  'Evidence: PUBLIC_THEME_AUDIT.json and screenshots-public-design/.',
  'This report contains observed browser values; no synthetic zero counters.',
  'Visual captures exclude only the Symfony developer toolbar; cookie consent is refused through the visible UI.',
  'To compare SEO metadata, supply OLING_AUDIT_BASELINE_FILE from a main-branch capture.', '',
].join('\n'));

const escapeHtml = (input) => String(input ?? '').replaceAll('&', '&amp;')
  .replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;');
const previews = records.filter(({ route, width, status }) =>
  status === 200 && (
    [390, 1366].includes(width) ||
    (['/a-propos', '/a-propos/team', '/en/about', '/en/team', '/es/quienes-somos', '/es/equipo'].includes(route))
  )
).map(({ route, width }) => {
  const name = `${route === '/' ? 'home' : route.slice(1).replaceAll('/', '-')}-${width}.png`;
  const file = join(out, 'screenshots-public-design', name);
  return { route, width, name, file };
}).filter(({ file }) => existsSync(file));
const galleryCards = previews.map(({ route, width, name }) => `
  <article class="preview-card">
    <div class="preview-card__heading"><strong>${escapeHtml(route)}</strong><span>${width} px</span></div>
    <a target="_blank" rel="noopener" href="screenshots-public-design/${encodeURIComponent(name)}">
      <img src="screenshots-public-design/${encodeURIComponent(name)}"
           alt="Capture réelle de ${escapeHtml(route)} à ${width} pixels"
           loading="lazy">
    </a>
  </article>
`).join('\n');
const gallery = `<!doctype html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>OLING — revue graphique locale</title>
<style>
  :root{color-scheme:light;font-family:system-ui,-apple-system,sans-serif}
  *{box-sizing:border-box}body{margin:0;background:#f4f7fb;color:#16334b}
  .hero{padding:2rem clamp(1rem,4vw,4rem);background:#132b40;color:#fff}
  .hero h1{margin:.25rem 0;font-size:clamp(1.6rem,3vw,2.6rem)}
  .hero p{max-width:65ch;color:#d7e4ef;line-height:1.6}
  main{max-width:1500px;margin:auto;padding:2rem clamp(.8rem,2vw,2rem)}
  .summary{padding:1rem 1.25rem;margin-bottom:1.5rem;border-radius:.75rem;background:#fff;border:1px solid #dce5ee}
  .summary strong{color:${failures.length ? '#af2949' : '#226449'}}
  .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,380px),1fr));gap:1.5rem}
  .preview-card{min-width:0;overflow:hidden;border:1px solid #dae4ee;border-radius:1rem;background:#fff;box-shadow:0 14px 35px -30px #123}
  .preview-card__heading{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.9rem 1.1rem}
  .preview-card__heading strong{overflow-wrap:anywhere}
  .preview-card__heading span{flex:none;font-size:.8rem;color:#60778c}
  .preview-card a{display:block;max-height:700px;overflow:auto;outline-offset:-3px}
  .preview-card img{display:block;width:100%;height:auto}
  .footnote{margin-top:2rem;color:#506b82;line-height:1.6}
</style></head>
<body><header class="hero"><h1>OLING — Prévisualisation de la refonte</h1>
<p>Captures produites par un véritable navigateur Playwright sur l'environnement local/de recette :
${escapeHtml(baseUrl)}. Elles ne sont ni des maquettes ni des images générées.</p>
${fixtureLabel ? `<p><strong>Données de recette :</strong> ${escapeHtml(fixtureLabel)}. Les contenus et images affichés peuvent différer de la production.</p>` : ''}</header>
<main><div class="summary"><strong>${failures.length} anomalie(s) détectée(s)</strong> sur ${records.length} combinaisons route/largeur ;
${previews.length} captures disponibles. ${failures.length ? 'Consulter PUBLIC_THEME_AUDIT.md avant toute validation.' : 'Contrôler aussi les interactions et le référencement avant accord.'}</div>
<section class="grid">${galleryCards}</section>
<p class="footnote">Les captures excluent uniquement la barre d'outils Symfony de développement ; le bandeau cookies est refusé par son vrai bouton dans ce navigateur de recette.</p>
<p class="footnote">Chaque capture est cliquable. Les images représentent l'état local à la date de l'audit, et non OLING.fr en production.
Pour tester les menus et les liens, ouvrir le site local directement dans un navigateur. Ne pas fusionner ou déployer sans validation.</p></main></body></html>`;
writeFileSync(join(out, 'preview.html'), gallery);
console.log(`Gallery generated: ${previews.length} actual screenshots at ${join(out, 'preview.html')}`);

for (const failure of failures) console.error(`VISUAL_QA_FAIL: ${failure}`);
console.log(`Visual audit: ${records.length} samples, ${failures.length} failures`);
if (failures.length) process.exitCode = 1;
