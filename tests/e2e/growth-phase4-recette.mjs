import { chromium } from 'playwright';
import fs from 'node:fs';

const baseUrl = process.env.OLING_BASE_URL || 'http://127.0.0.1:8061';
const username = process.env.OLING_ADMIN_USERNAME || 'phase4';
const password = process.env.OLING_ADMIN_PASSWORD || 'testpass';
const outDir = new URL('../../docs/growth/screenshots/', import.meta.url);
fs.mkdirSync(outDir, { recursive: true });
const runId = new Date().toISOString().replace(/[-:.TZ]/g, '').slice(0, 14);

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
const channelsByType = {
  OLING: ['oling_site', 'linkedin_manual', 'email_manual'],
  MAPSI_USERS: ['mapsi_site', 'linkedin_manual', 'email_manual'],
  MAPSI_MARKETING: ['mapsi_site', 'oling_site', 'linkedin_manual', 'email_manual'],
};

async function login() {
  await page.goto(`${baseUrl}/login`);
  await page.fill('input[name="_username"]', username);
  await page.fill('input[name="_password"]', password);
  await page.click('button');
  await page.waitForURL(/\/admin/);
}

async function createCampaign(type, theme) {
  await page.goto(`${baseUrl}/admin/growth`);
  await page.selectOption('#growth-type', type);
  for (const channel of channelsByType[type]) {
    await page.locator(`input[name="channels[]"][value="${channel}"]`).check({ force: true });
  }
  await page.fill('#growth-theme', theme);
  await page.click('button:has-text("Créer et générer")');
  await page.waitForURL(/\/admin\/growth\/campaigns\//, { timeout: 120000 });
  await page.waitForSelector('textarea[name="content_text"]', { timeout: 30000 });
  return page.url();
}

async function updateFirstAsset(text) {
  const form = page.locator('form[action*="/assets/"]').first();
  const oldVersion = await form.locator('input[name="expected_version"]').inputValue();
  const action = await form.getAttribute('action');
  const token = await form.locator('input[name="_token"]').inputValue();
  await form.locator('textarea[name="content_text"]').fill(text);
  await form.locator('button:has-text("Enregistrer ce canal")').click();
  await page.waitForLoadState('networkidle');
  return { action, token, oldVersion };
}

async function staleUpdate({ action, token, oldVersion }) {
  return page.request.post(`${baseUrl}${action}`, {
    form: {
      _token: token,
      title: 'Conflit volontaire',
      content_text: 'Version obsolete',
      content_html: '<p>Version obsolete</p>',
      expected_version: oldVersion,
    },
    maxRedirects: 0,
  });
}

async function publishCurrent() {
  page.once('dialog', dialog => dialog.accept());
  await page.click('button:has-text("Publier les canaux")');
  await page.waitForLoadState('networkidle');
}

async function deleteCurrent() {
  page.once('dialog', dialog => dialog.accept());
  await page.click('button:has-text("Supprimer / archiver")');
  await page.waitForURL(/\/admin\/growth\/?$/);
}

try {
  await login();
  await page.goto(`${baseUrl}/admin`);
  await page.click('a:has-text("Growth")');
  await page.waitForURL(/\/admin\/growth/);
  await page.screenshot({ path: new URL('growth-index-desktop.png', outDir).pathname, fullPage: true });

  await createCampaign('OLING', `Phase 4 recette OLING ${runId}`);
  await page.screenshot({ path: new URL('growth-oling-show.png', outDir).pathname, fullPage: true });
  const stale = await updateFirstAsset('Texte modifie pendant la recette Phase 4.');
  const staleResponse = await staleUpdate(stale);
  if (![302, 303].includes(staleResponse.status())) {
    throw new Error(`Unexpected stale update HTTP ${staleResponse.status()}`);
  }
  await page.reload();
  await page.click('a:has-text("Preview")');
  await page.waitForLoadState('networkidle');
  await page.screenshot({ path: new URL('growth-preview.png', outDir).pathname, fullPage: true });
  await page.goBack();
  await publishCurrent();

  await createCampaign('MAPSI_USERS', `Phase 4 recette MAPSI users ${runId}`);
  await deleteCurrent();

  await createCampaign('MAPSI_MARKETING', `Phase 4 recette MAPSI marketing ${runId}`);
  await publishCurrent();
  await deleteCurrent();

  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(`${baseUrl}/admin/growth`);
  await page.screenshot({ path: new URL('growth-index-mobile.png', outDir).pathname, fullPage: true });

  console.log('growth phase4 browser recette ok');
} finally {
  await browser.close();
}
