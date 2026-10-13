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
const widths = [360, 390, 768, 1024, 1366, 1440, 1920];
const failures = [], records = [];
const seoKeys = ['title', 'description', 'canonical', 'robots', 'hreflang', 'ogTitle', 'ogDescription'];
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
            overflow: document.documentElement.scrollWidth - window.innerWidth,
            header: nodeInfo('#header'),
            topbar: nodeInfo('#header .oling-topbar'),
            hero: nodeInfo('main section.oling-shell-hero'),
            heroPanel: nodeInfo('.oling-shell-hero__inner'),
            aboutCards: document.querySelectorAll('.oling-leadership-grid--preview .oling-leadership-card').length,
            teamNames: [...document.querySelectorAll('.oling-leadership-grid--detail .oling-leadership-card__name')].map((el) => el.textContent.trim()),
            personNames: schemaNames,
          };
        });
        const seo = Object.fromEntries(seoKeys.map((key) => [key, data[key]]));
        const previous = baseline?.records.find((r) => r.route === route && r.width === width);
        if (baseline) {
          if (!previous?.seo) {
            issue('SEO baseline record missing');
          } else {
            for (const key of seoKeys) {
              if (JSON.stringify(seo[key]) !== JSON.stringify(previous.seo[key])) issue(`SEO ${key} changed`);
            }
          }
        }
        if (!data.canonical || !data.description) issue('Missing canonical or description');
        if (data.h1.length !== 1) issue(`Expected one H1; found ${data.h1.length}`);
        if (data.overflow > 3) issue(`Horizontal overflow ${data.overflow}px`);
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
        if (kind === 'about' || kind === 'team' || [390, 1366].includes(width)) {
          await page.screenshot({
            path: join(out, 'screenshots-public-design', `${route === '/' ? 'home' : route.slice(1).replaceAll('/', '-')}-${width}.png`),
            fullPage: true, animations: 'disabled',
          });
        }
        if (!captureBaseline && kind !== 'home' && width < 992) {
          const toggle = page.locator('#header .navbar-toggler').first();
          if (await toggle.isVisible()) {
            await toggle.click();
            await page.waitForTimeout(350);
            if (!await page.locator('#navbarNavMenuLeftAligned').evaluate((el) => el.classList.contains('show'))) {
              issue('Mobile menu did not expand');
            }
          }
        }
        records.push({ route, width, status, seo, h1: data.h1, layout: {
          header: data.header, topbar: data.topbar, hero: data.hero,
          heroPanel: data.heroPanel, horizontalOverflow: data.overflow,
        }, aboutCards: data.aboutCards, teamNames: data.teamNames, personNames: data.personNames });
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
const report = { timestamp: new Date().toISOString(), baseUrl, captureBaseline, baselinePath, records, failures };
writeFileSync(join(out, 'PUBLIC_THEME_AUDIT.json'), JSON.stringify(report, null, 2) + '\n');
writeFileSync(join(out, 'PUBLIC_THEME_AUDIT.md'), [
  '# OLING Public Theme — browser computed-style audit', '',
  `Base: ${baseUrl}`,
  `Audit mode: ${captureBaseline ? 'baseline capture' : 'new design validation'}`,
  `Routes/viewport combinations: ${records.length}`,
  `Failures: ${failures.length}`, '',
  ...(failures.length ? failures.map((f) => `- ${f}`) : ['All sampled browser assertions passed.']), '',
  'Evidence: PUBLIC_THEME_AUDIT.json and screenshots-public-design/.',
  'This report contains observed browser values; no synthetic zero counters.',
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
${escapeHtml(baseUrl)}. Elles ne sont ni des maquettes ni des images générées.</p></header>
<main><div class="summary"><strong>${failures.length} anomalie(s) détectée(s)</strong> sur ${records.length} combinaisons route/largeur ;
${previews.length} captures disponibles. ${failures.length ? 'Consulter PUBLIC_THEME_AUDIT.md avant toute validation.' : 'Contrôler aussi les interactions et le référencement avant accord.'}</div>
<section class="grid">${galleryCards}</section>
<p class="footnote">Chaque capture est cliquable. Les images représentent l'état local à la date de l'audit, et non OLING.fr en production.
Pour tester les menus et les liens, ouvrir le site local directement dans un navigateur. Ne pas fusionner ou déployer sans validation.</p></main></body></html>`;
writeFileSync(join(out, 'preview.html'), gallery);
console.log(`Gallery generated: ${previews.length} actual screenshots at ${join(out, 'preview.html')}`);

console.log(`Visual audit: ${records.length} samples, ${failures.length} failures`);
if (failures.length) process.exitCode = 1;
