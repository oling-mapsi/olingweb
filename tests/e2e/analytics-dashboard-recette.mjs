import { chromium } from 'playwright';
import { mkdirSync } from 'node:fs';

const baseUrl = process.env.OLING_BASE_URL || 'http://127.0.0.1:8013';
const username = process.env.OLING_ADMIN_USER;
const password = process.env.OLING_ADMIN_PASSWORD;
const outputDir = process.env.OLING_RECETTE_DIR || 'var/recette';

if (!username || !password) {
  throw new Error('Missing OLING_ADMIN_USER or OLING_ADMIN_PASSWORD.');
}

mkdirSync(outputDir, { recursive: true });

const browser = await chromium.launch({ headless: true });
const results = [];

async function capture(name, viewport) {
  const context = await browser.newContext({ viewport });
  const page = await context.newPage();
  const jsErrors = [];

  page.on('pageerror', (error) => jsErrors.push(error.message));

  await page.goto(`${baseUrl}/login`, { waitUntil: 'networkidle' });
  await page.fill('input[name="_username"]', username);
  await page.fill('input[name="_password"]', password);
  await page.getByRole('button', { name: 'Se connecter' }).click();
  await page.waitForLoadState('networkidle');
  if (!page.url().includes('/admin')) {
    throw new Error(`Login failed, current URL: ${page.url()}`);
  }
  await page.goto(`${baseUrl}/admin/analytics?period=30`, { waitUntil: 'networkidle' });
  await page.getByRole('heading', { name: 'Audience & Parcours' }).waitFor({ timeout: 10000 });
  await page.addStyleTag({
    content: '.sf-toolbar, [id^="sfwdt"], [id^="sfMiniToolbar"] { display: none !important; }',
  });

  const dataExcluded = await page.locator('body').getAttribute('data-analytics-excluded');
  const path = `${outputDir}/analytics-dashboard-${name}.png`;
  await page.screenshot({ path, fullPage: true });
  results.push({ name, path, dataExcluded, jsErrors });
  await context.close();
}

await capture('mobile-375', { width: 375, height: 1200 });
await capture('tablet-768', { width: 768, height: 1200 });
await capture('desktop-1440', { width: 1440, height: 1100 });
await browser.close();

console.log(JSON.stringify(results, null, 2));
