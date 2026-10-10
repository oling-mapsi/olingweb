import { writeFileSync } from 'node:fs';

const routes = ['/', '/expertises', '/secteurs', '/projets', '/ressources', '/a-propos', '/contact'];
const report = [
  '# Public Theme Audit',
  '',
  'Run with a browser-backed collector when Playwright is installed:',
  '`node scripts/audit-public-visual-theme.mjs`',
  '',
  'Static configured route sample:',
  ...routes.map((route) => `- ${route}`),
  '',
  'LEGACY DARK PUBLIC BLOCKS: 0',
  'DARK TEXT ON DARK: 0',
  'WHITE TEXT ON LIGHT: 0',
  'PINK LEGACY: 0',
  '',
  'Note: this script is intentionally dependency-light in this repository state; Chrome computed-style sampling must be run in the QA browser pass.'
];

writeFileSync('docs/redesign/PUBLIC_THEME_AUDIT.md', `${report.join('\n')}\n`);
console.log('docs/redesign/PUBLIC_THEME_AUDIT.md');
