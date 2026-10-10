const { test, expect } = require('@playwright/test');

const token = 'test-chat-ui-token';

const welcomeMessage = {
  role: 'assistant',
  content: 'Bonjour. Je suis l’assistant expert OLING.',
  type: 'welcome',
  sources: [],
  sourceCards: [],
  actions: [],
};

const sageReply = {
  role: 'assistant',
  type: 'lead_request',
  status: 'llm_primary',
  content: [
    'Analyse Sage X3 :',
    'Votre ETI industrielle de 450 salariés semble rencontrer un sujet transverse stocks, achats et CRM. Avant de décider une optimisation ou un remplacement, OLING cadrerait les flux, les irritants métier et la trajectoire SI.',
    'Priorités :',
    '- Cartographier les processus stocks, achats et relation client.',
    '- Comparer optimisation Sage X3 et remplacement ciblé du CRM.',
    '- Définir une architecture cible réaliste et gouvernable.',
    '- Sécuriser une AMOA indépendante avant consultation.',
    'Vous pouvez contacter OLING au 01 89 70 15 60 ou écrire à contact@oling.fr.',
  ].join('\n'),
  sources: [],
  sourceCards: [
    {
      url: '/expertises/transformation-si',
      title: 'Transformation SI',
      type: 'expertise',
      excerpt: 'Cadrage, trajectoire applicative et appui AMOA pour systèmes d’information.',
    },
    {
      url: '/references/industrie-erp',
      title: 'Références ERP industrie',
      type: 'reference',
      excerpt: 'Missions OLING autour des ERP et progiciels industriels.',
    },
  ],
  actions: [
    { type: 'start_diagnostic', label: 'Lancer un mini-diagnostic' },
    { type: 'open_lead_form', label: 'Transmettre mon projet à OLING' },
  ],
};

const diagnosticReply = {
  role: 'assistant',
  type: 'diagnostic',
  status: 'llm_primary',
  content: 'Mini-diagnostic : contexte Maison&Objet, remplacement CRM, architecture cible, besoin d’AMOA indépendante. Risques : reprise de données, adoption, gouvernance.',
  sources: [],
  sourceCards: [],
  actions: [
    { type: 'generate_scoping_note', label: 'Préparer ma note de cadrage' },
    { type: 'open_lead_form', label: 'Échanger avec un consultant OLING' },
  ],
};

const scopingReply = {
  role: 'assistant',
  type: 'scoping_note',
  status: 'llm_primary',
  content: 'Note de cadrage : Maison&Objet souhaite transformer son SI commercial, remplacer le CRM, définir une architecture cible et sécuriser une AMOA indépendante. Document indicatif, modifiable et à valider avant transmission.',
  sources: [],
  sourceCards: [],
  actions: [
    { type: 'download_scoping_note', label: 'Télécharger la note' },
    { type: 'open_lead_form', label: 'Transmettre à OLING' },
  ],
};

const markdownReply = {
  role: 'assistant',
  type: 'proposal_request',
  status: 'llm_primary',
  content: [
    '## Synthèse AMOA ERP',
    '',
    '**Méthodologie** : OLING propose d’abord de cadrer l’existant, puis de sécuriser l’aide au choix avec une AMOA indépendante. Voir [l’expertise ERP OLING](/business-apps/erp).',
    '',
    '- Cartographier les processus études, achats, stocks, production, qualité et ventes.',
    '- Préparer un cahier des charges allégé avec les critères de choix.',
    '',
    '1. Ateliers métiers et cadrage des irritants.',
    '2. Analyse comparative et recommandation.',
    '',
    'L’équipe d’OLING conserve les accents, l’apostrophe et la mise en forme.',
    '',
    '<script>window.__olingInjected = true</script>',
  ].join('\n'),
  sources: ['/business-apps/erp'],
  sourceCards: [
    { url: '/business-apps/erp', title: 'ERP et progiciels', type: 'service', excerpt: 'Cadrage ERP, choix de solution et trajectoire.' },
  ],
  actions: [{ type: 'open_lead_form', label: 'Recevoir une proposition OLING' }],
};

test.beforeEach(async ({ page }) => {
  await page.addInitScript(() => window.localStorage.clear());

  await page.route('**/api/chat/conversations', async (route) => {
    if (route.request().method() !== 'POST') return route.fallback();
    await route.fulfill({
      contentType: 'application/json',
      body: JSON.stringify({
        token,
        status: 'active',
        messages: [welcomeMessage],
        leadSubmitted: false,
        requestLead: false,
        qualification: {},
        contact: null,
      }),
    });
  });

  await page.route(`**/api/chat/conversations/${token}`, async (route) => {
    if (route.request().method() !== 'GET') return route.fallback();
    await route.fulfill({
      contentType: 'application/json',
      body: JSON.stringify({
        token,
        status: 'active',
        messages: [welcomeMessage],
        leadSubmitted: false,
        requestLead: false,
        qualification: {},
        contact: null,
      }),
    });
  });

  await page.route(`**/api/chat/conversations/${token}/messages`, async (route) => {
    const payload = route.request().postDataJSON();
    const content = payload.content || '';
    const reply = content.includes('markdown') ? markdownReply : (content.includes('note de cadrage') ? scopingReply : (content.includes('mini-diagnostic') ? diagnosticReply : sageReply));
    await route.fulfill({
      contentType: 'application/json',
      body: JSON.stringify({
        success: true,
        reply,
        conversation: {
          token,
          status: 'lead_pending',
          messages: [
            welcomeMessage,
            { role: 'visitor', content },
            reply,
          ],
          leadSubmitted: false,
          requestLead: true,
          qualification: { primary_need: 'crm', organization_name: 'Maison&Objet', commercial_intent: 'transformation' },
          contact: null,
        },
      }),
    });
  });
});

test('Rendu Markdown assistant avec paragraphes, listes, gras, lien et échappement HTML', async ({ page }) => {
  await page.goto('/');
  await page.locator('#oling-chat-widget').evaluate((root) => root.querySelector('.oling-chat-widget__launcher').click());

  await page.locator('textarea[name="chatMessage"]').fill('Test markdown ERP avec accents et apostrophes.');
  await page.locator('.oling-chat-widget__send').click();

  const assistant = page.locator('#oling-chat-widget .oling-chat-widget__assistant-body').last();
  await expect(assistant.locator('.oling-chat-widget__assistant-heading', { hasText: 'Synthèse AMOA ERP' })).toBeVisible();
  await expect(assistant.locator('strong', { hasText: 'Méthodologie' })).toBeVisible();
  await expect(assistant.locator('a[href="/business-apps/erp"]', { hasText: 'l’expertise ERP OLING' })).toBeVisible();
  await expect(assistant.locator('ul li')).toHaveCount(2);
  await expect(assistant.locator('ol li')).toHaveCount(2);
  await expect(assistant).toContainText('L’équipe d’OLING conserve les accents');
  await expect(assistant.locator('script')).toHaveCount(0);
  await expect(assistant).toContainText('<script>window.__olingInjected = true</script>');
  await expect(page.evaluate(() => window.__olingInjected === true)).resolves.toBe(false);
  await expect(page.locator('#oling-chat-widget .oling-chat-widget__assistant-action', { hasText: 'Recevoir une proposition OLING' })).toBeVisible();
});

test('Maison&Objet enchaine mini-diagnostic, note et formulaire prérempli', async ({ page }) => {
  await page.goto('/');
  await page.locator('#oling-chat-widget').evaluate((root) => root.querySelector('.oling-chat-widget__launcher').click());

  await page.locator('textarea[name="chatMessage"]').fill('Maison&Objet veut transformer son SI commercial, remplacer le CRM, définir une architecture cible et cadrer une AMOA indépendante.');
  await page.locator('.oling-chat-widget__send').click();

  await page.locator('.oling-chat-widget__assistant-action', { hasText: 'Lancer un mini-diagnostic' }).click();
  await expect(page.locator('#oling-chat-widget')).toContainText('Mini-diagnostic');
  await page.locator('.oling-chat-widget__assistant-action', { hasText: 'Préparer ma note de cadrage' }).click();
  await expect(page.locator('#oling-chat-widget')).toContainText('Note de cadrage');
  await page.locator('.oling-chat-widget__assistant-action', { hasText: 'Transmettre à OLING' }).click();

  const description = page.locator('textarea[name="chatNeedDescription"]');
  await expect(description).toBeVisible();
  await expect(description).toHaveValue(/Maison&Objet/);
  await expect(description).toHaveValue(/remplacer le CRM/);
  await expect(page.locator('#oling-chat-widget')).not.toContainText('/contact?chat_fallback=1');
});

test('Sage X3 affiche un widget lisible, compact et sans débordement', async ({ page }, testInfo) => {
  await page.goto('/');
  await page.locator('#oling-chat-widget').evaluate((root) => root.querySelector('.oling-chat-widget__launcher').click());

  const input = page.locator('textarea[name="chatMessage"]');
  await input.fill('ETI industrielle 450 salariés avec Sage X3, tensions stocks achats CRM.');
  await page.locator('.oling-chat-widget__send').click();

  const widget = page.locator('#oling-chat-widget');
  const panel = page.locator('.oling-chat-widget__panel');
  const assistantBody = page.locator('.oling-chat-widget__assistant-body').last();
  const sources = page.locator('.oling-chat-widget__sources-inline').last();
  const cta = page.locator('.oling-chat-widget__assistant-action', { hasText: 'Transmettre mon projet à OLING' });

  await expect(widget).toContainText('Analyse Sage X3');
  await expect(widget).toContainText('Sources utiles');
  await expect(sources.locator('.oling-chat-widget__source-card')).toHaveCount(2);
  await expect(widget).not.toContainText('127.0.0.1');
  await expect(widget).not.toContainText('/contact?chat_fallback=1');
  await expect(page.locator('#oling-chat-widget a[href="tel:0189701560"]')).toBeVisible();
  await expect(page.locator('#oling-chat-widget a[href="mailto:contact@oling.fr"]')).toBeVisible();
  await expect(cta).toBeVisible();

  const viewport = page.viewportSize();
  const box = await panel.boundingBox();
  expect(box).toBeTruthy();
  expect(Math.round(box.x + box.width)).toBeLessThanOrEqual(viewport.width);
  expect(Math.round(box.width)).toBeLessThanOrEqual(viewport.width);
  if (viewport.width >= 900) {
    expect(Math.round(box.width)).toBeGreaterThanOrEqual(480);
    expect(Math.round(box.width)).toBeLessThanOrEqual(540);
  }

  const metrics = await assistantBody.evaluate((node) => {
    const style = getComputedStyle(node);
    const panelStyle = getComputedStyle(node.closest('.oling-chat-widget__panel'));
    const root = document.documentElement;
    return {
      color: style.color,
      background: style.backgroundColor,
      panelBackground: panelStyle.backgroundColor,
      borderWidth: style.borderWidth,
      fontSize: style.fontSize,
      lineHeight: style.lineHeight,
      pageOverflow: root.scrollWidth - window.innerWidth,
    };
  });
  expect(metrics.background).toBe('rgba(0, 0, 0, 0)');
  expect(metrics.panelBackground).toBe('rgb(255, 255, 255)');
  expect(metrics.borderWidth).toBe('0px');
  expect(metrics.color).toBe('rgb(16, 24, 32)');
  expect(metrics.fontSize).toBe('15px');
  expect(parseFloat(metrics.lineHeight)).toBeGreaterThanOrEqual(23);
  expect(metrics.pageOverflow).toBeLessThanOrEqual(1);

  const ctaBox = await cta.boundingBox();
  expect(ctaBox.height).toBeGreaterThanOrEqual(40);
  await expect(input).toBeVisible();

  await page.screenshot({
    path: `test-results/chat-v2-4-${testInfo.project.name}.png`,
    fullPage: true,
  });
});
