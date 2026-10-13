/**
 * Compare actual rendered SEO metadata on main and this PR using the same
 * isolated MySQL fixture. Fail on uncontrolled metadata or title changes.
 *
 * node scripts/ci/compare-public-seo-snapshots.mjs \
 *   docs/redesign/seo-main/PUBLIC_THEME_AUDIT.json \
 *   docs/redesign/PUBLIC_THEME_AUDIT.json \
 *   docs/redesign/SEO_DIFF_REPORT.md
 */
import { readFileSync, writeFileSync } from 'node:fs';

const [baselinePath, proposedPath, outputPath] = process.argv.slice(2);
if (!baselinePath || !proposedPath || !outputPath) {
  throw new Error('Usage: compare-public-seo-snapshots.mjs baseline.json proposal.json report.md');
}
const baseline = JSON.parse(readFileSync(baselinePath, 'utf8'));
const proposed = JSON.parse(readFileSync(proposedPath, 'utf8'));
const fields = ['title', 'description', 'canonical', 'robots', 'hreflang', 'ogTitle', 'ogDescription'];
const excludedRoutes = new Set(
  (process.env.OLING_SEO_EXCLUDED_ROUTES || '').split(',').map((value) => value.trim()).filter(Boolean)
);
const normalizeUrl = (value) => {
  if (typeof value !== 'string') return value;
  try {
    const url = new URL(value);
    if (['127.0.0.1', 'localhost', '[::1]'].includes(url.hostname)) {
      return url.pathname + url.search + url.hash;
    }
  } catch { /* strings that are not URLs must remain exact */ }
  return value;
};
const normalizeField = (field, value) => field === 'canonical'
  ? normalizeUrl(value)
  : field === 'hreflang' && value && typeof value === 'object'
    ? Object.fromEntries(Object.entries(value).map(([key, url]) => [key, normalizeUrl(url)]).sort(([a],[b]) => a.localeCompare(b)))
    : value;

const byKey = new Map(baseline.records
  .filter((r) => r.width === 1366 && !excludedRoutes.has(r.route))
  .map((r) => [r.route, r]));
const errors = [];
const editorialExceptions = [];
const comparisons = [];
let validatedMetadata = 0;
for (const candidate of proposed.records.filter((r) => r.width === 1366 && !excludedRoutes.has(r.route))) {
  const base = byKey.get(candidate.route);
  if (!base || base.status !== 200 || candidate.status !== 200 || !base.seo || !candidate.seo) {
    errors.push(`${candidate.route}: missing same-data HTTP 200 baseline/proposal metadata`);
    continue;
  }
  const differences = [];
  for (const field of fields) {
    const before = normalizeField(field, base.seo[field]);
    const after = normalizeField(field, candidate.seo[field]);
    if (JSON.stringify(before) !== JSON.stringify(after)) {
      errors.push(`${candidate.route}: uncontrolled SEO metadata changed: ${field} (${JSON.stringify(before)} -> ${JSON.stringify(after)})`);
      differences.push(field);
    } else {
      validatedMetadata++;
    }
  }

  if (JSON.stringify(base.h1 || []) !== JSON.stringify(candidate.h1 || [])) {
    if (['/a-propos/team', '/en/team', '/es/equipo'].includes(candidate.route)) {
      editorialExceptions.push(`${candidate.route}: deliberate visible H1 editorial change; old=${JSON.stringify(base.h1)}; new=${JSON.stringify(candidate.h1)}`);
    } else {
      errors.push(`${candidate.route}: unexpected H1 change: ${JSON.stringify(base.h1)} -> ${JSON.stringify(candidate.h1)}`);
    }
  }

  if (JSON.stringify(base.personNames || []) !== JSON.stringify(candidate.personNames || [])) {
    if (['/a-propos/team', '/en/team', '/es/equipo'].includes(candidate.route)) {
      editorialExceptions.push(`${candidate.route}: public Person schema follows the deliberately reduced leadership roster (${(base.personNames || []).length} -> ${(candidate.personNames || []).length}); confirm indexation/legacy anchors`);
    } else {
      errors.push(`${candidate.route}: unexpected Person JSON-LD list change`);
    }
  }

  comparisons.push({ route: candidate.route, metadataFieldsUnchanged: fields.length - differences.length, metadataChanges: differences });
  byKey.delete(candidate.route);
}
if (byKey.size) errors.push(`Routes present only in baseline: ${[...byKey.keys()].join(', ')}`);

const result = {
  date: new Date().toISOString(),
  baselinePath,
  proposedPath,
  routesCompared: comparisons.length,
  excludedRoutes: [...excludedRoutes],
  metadataFieldsUnchanged: validatedMetadata,
  editorialExceptions,
  errors,
  comparisons,
};
const esc = (s) => String(s).replaceAll('|', '\\|').replaceAll('\n', ' ');
const markdown = [
  '# OLING.fr — comparison of rendered SEO: main vs redesign',
  '',
  'This report compares pages rendered from the **same disposable, versioned CI MySQL fixture**.',
  `Explicitly excluded from differential SEO comparison: ${[...excludedRoutes].join(', ') || 'none'} (excluded pages must be browser-checked independently).`,
  'It is not a production Google indexation, crawl-volume or Search Console verification.',
  '',
  `Pages compared at 1366px: **${result.routesCompared}**`,
  `Identical metadata fields: **${validatedMetadata}**`,
  `Unexpected SEO/H1/Person regressions: **${errors.length}**`,
  `Intentional editorial deltas needing sign-off: **${editorialExceptions.length}**`,
  '',
  '| URL | Unchanged metadata / 7 | Meta changes |',
  '|---|---:|---|',
  ...comparisons.map((c) => `| ${esc(c.route)} | ${c.metadataFieldsUnchanged} | ${c.metadataChanges.length ? esc(c.metadataChanges.join(', ')) : 'None'} |`),
  '',
  '## Deliberate editorial changes (NOT SEO-neutral)',
  '',
  ...(editorialExceptions.length ? editorialExceptions.map((line) => `- ${line}`) : ['- None.']),
  '',
  '## Uncontrolled regressions',
  '',
  ...(errors.length ? errors.map((line) => `- ${line}`) : ['- No uncontrolled metadata, H1 or Person-schema changes detected in the sampled routes.']),
  '',
  '## Follow-up prior to production',
  '',
  '- Confirm sitemap/robots/redirect routes from the live target.',
  '- Review inbound links to old Team person anchors, and assess editorial impact.',
  '- Check pages not in this sampling set and final public assets, including real sector photos.',
  '- Obtain explicit graphical and SEO publication sign-off.',
  '',
].join('\n');
writeFileSync(outputPath, markdown);
writeFileSync(outputPath.replace(/\.md$/, '.json'), JSON.stringify(result, null, 2) + '\n');
console.log(`SEO diff: ${comparisons.length} routes, ${validatedMetadata} metadata fields unchanged, ${errors.length} regression(s), ${editorialExceptions.length} deliberate editorial exception(s)`);
if (errors.length) process.exitCode = 1;
