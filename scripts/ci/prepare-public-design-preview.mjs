/**
 * Derive strictly-local preview translation fixtures from reviewed, versioned
 * snapshots. Never connects to any site or database, never edits tracked input.
 *
 * This CI utility is NOT a content import workflow for production.
 */
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { resolve } from 'node:path';

const allowed = new Set(['apropos', 'team', 'projets', 'metiers', 'secteurs-index', 'expertises-index', 'ressources']);
const destination = resolve('var/public-design-preview');
mkdirSync(destination, { recursive: true });
for (const locale of ['en', 'es']) {
  const source = resolve(`data/i18n/waves/site_pages.wave1.${locale}.json`);
  const snapshot = JSON.parse(readFileSync(source, 'utf8'));
  if (snapshot.locale !== locale || !Array.isArray(snapshot.translations)) {
    throw new Error(`Invalid source translation snapshot for ${locale}`);
  }
  snapshot.translations = snapshot.translations.filter((row) => allowed.has(row.source?.slug));
  const leadership = snapshot.translations.filter((row) => ['apropos', 'team'].includes(row.source?.slug));
  if (leadership.length !== 2 || leadership.some((row) => row.status !== 'published')) {
    throw new Error(`Expected two published institutional pages for ${locale}`);
  }
  const target = resolve(destination, `site-pages.${locale}.json`);
  writeFileSync(target, JSON.stringify(snapshot, null, 2) + '\n');
  console.log(`${locale}: ${snapshot.translations.length} curated snapshot rows -> ${target}`);
}
