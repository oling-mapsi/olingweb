const { test, expect } = require('@playwright/test');

const token = 'test-chat-conversion-token';

const welcomeMessage = {
  role: 'assistant',
  content: 'Bonjour. Je suis l’assistant expert OLING.',
  type: 'welcome',
  sources: [],
  sourceCards: [],
  actions: [],
};

const erpProposalMessage = 'Bonjour, PME industrielle, environ 40 utilisateurs ERP. Nous cherchons une mission courte d’AMOA ERP pour cadrage projet, ateliers métiers, cartographie des processus études, achats, approvisionnements, stocks, production, qualité, ventes et pilotage, expression des besoins, cahier des charges allégé, préparation de consultation, démonstrations éditeurs, analyse comparative et recommandation. Pouvez-vous nous donner votre méthodologie, le nombre de jours estimés, les livrables, des références industrielles comparables et le coût de la mission ?';

const replies = {
  dora: {
    role: 'assistant',
    type: 'contact_offer',
    status: 'llm_primary',
    content: 'Oui. OLING accompagne la mise sous contrôle des exigences DORA. Je vous propose un premier échange avec un consultant OLING pour examiner votre situation.',
    sources: ['/cybersecurite', '/pca-pra-continuite-activite'],
    sourceCards: [
      { url: '/cybersecurite', title: 'Cybersécurité', type: 'service', excerpt: 'Gouvernance, risques, conformité et résilience.' },
      { url: '/pca-pra-continuite-activite', title: 'Continuité d’activité', type: 'service', excerpt: 'Résilience opérationnelle et plans d’action.' },
    ],
    actions: [{ type: 'open_lead_form', label: 'Être recontacté par OLING' }],
    qualification: {
      primary_need: 'cybersecurite',
      commercial_intent: 'mise_en_conformite',
      maturity_level: 'cadre',
    },
  },
  dpo: {
    role: 'assistant',
    type: 'contact_offer',
    status: 'llm_primary',
    content: 'OLING peut structurer votre DPO externalisé. Je vous propose un premier échange avec un consultant OLING.',
    sources: ['/rgpd-dpo'],
    sourceCards: [],
    actions: [{ type: 'open_lead_form', label: 'Être recontacté par OLING' }],
    qualification: { primary_need: 'rgpd', commercial_intent: 'contact' },
  },
  finance: {
    role: 'assistant',
    type: 'contact_offer',
    status: 'llm_primary',
    content: 'OLING peut cadrer votre SI Finance et vos flux de reporting. Je vous propose un premier échange avec un consultant OLING.',
    sources: ['/si-finance'],
    sourceCards: [],
    actions: [{ type: 'open_lead_form', label: 'Être recontacté par OLING' }],
    qualification: { primary_need: 'si_finance', commercial_intent: 'cadrage' },
  },
  doraInfo: {
    role: 'assistant',
    type: 'question',
    status: 'llm_primary',
    content: 'DORA est le cadre européen de résilience opérationnelle numérique.',
    sources: ['/cybersecurite'],
    sourceCards: [],
    actions: [],
    qualification: { primary_need: 'cybersecurite', commercial_intent: 'information' },
  },
  student: {
    role: 'assistant',
    type: 'question',
    status: 'llm_primary',
    content: 'L’AMOA signifie Assistance à Maîtrise d’Ouvrage.',
    sources: ['/amoa-si'],
    sourceCards: [],
    actions: [],
    qualification: { primary_need: 'amoa_erp', commercial_intent: 'information' },
  },
  erpProposal: {
    role: 'assistant',
    type: 'proposal_request',
    status: 'llm_primary',
    content: 'Pour cette mission AMOA ERP, OLING peut structurer la méthode, les livrables, une estimation de charge et les références industrielles mobilisables dans une proposition dédiée.',
    sources: ['/business-apps/erp'],
    sourceCards: [],
    actions: [{ type: 'open_lead_form', label: 'Recevoir une proposition OLING' }],
    qualification: { primary_need: 'amoa_erp', commercial_intent: 'quote_request', maturity_level: 'consultation', organization_type: 'pme', organization_size: '1_49' },
  },
  erpUnavailable: {
    role: 'assistant',
    type: 'technical_unavailable',
    status: 'llm_unavailable',
    content: 'Je rencontre momentanément une difficulté technique pour préparer votre réponse détaillée.\n\nVotre demande de mission AMOA ERP peut néanmoins être transmise directement à notre équipe, avec votre cahier des charges déjà renseigné.\n\nUn consultant OLING pourra ainsi examiner votre besoin et préparer une proposition adaptée.',
    sources: [],
    sourceCards: [],
    actions: [{ type: 'open_lead_form', label: 'Transmettre ma demande de proposition à OLING' }],
    qualification: { primary_need: 'amoa_erp', commercial_intent: 'quote_request', maturity_level: 'consultation', organization_type: 'pme', organization_size: '1_49' },
  },
  erpInfo: {
    role: 'assistant',
    type: 'question',
    status: 'llm_primary',
    content: 'Une AMOA ERP aide à cadrer le besoin métier, préparer la consultation et sécuriser le choix de solution.',
    sources: ['/business-apps/erp'],
    sourceCards: [],
    actions: [],
    qualification: { primary_need: 'amoa_erp', commercial_intent: 'information' },
  },
};

const resolveReply = (content) => {
  const text = content.toLowerCase();
  if (text.includes('pme industrielle') && text.includes('méthodologie')) return replies.erpProposal;
  if (text.includes('qu’est-ce qu’une amoa erp') || text.includes('c’est quoi une amoa erp')) return replies.erpInfo;
  if (text.includes('étudiant') || text.includes('définir')) return replies.student;
  if (text.includes('dora') && text.includes('c’est quoi')) return replies.doraInfo;
  if (text.includes('dpo')) return replies.dpo;
  if (text.includes('si finance')) return replies.finance;
  return replies.dora;
};

const expectErpProposalPrefill = async (page) => {
  const form = page.locator('[data-chat-lead]');
  await expect(form).toBeVisible();
  const description = form.locator('textarea[name="chatNeedDescription"]');
  await expect(description).toHaveValue(/Demande de proposition/);
  await expect(description).toHaveValue(/PME industrielle, environ 40 utilisateurs ERP/);
  await expect(description).toHaveValue(/études, achats, approvisionnements, stocks, production, qualité, ventes et pilotage/);
  await expect(description).toHaveValue(/méthodologie, nombre de jours estimés, livrables, références industrielles et coût/);
  await expect(description).toHaveValue(/texte original/i);
  await expect(description).toHaveValue(/cahier des charges allégé/);
  await expect(form.locator('input[name="chatPhone"]')).toHaveValue('');
  await expect(form.locator('input[name="chatConsent"]')).not.toBeChecked();
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
    const reply = resolveReply(content);
    await route.fulfill({
      contentType: 'application/json',
      body: JSON.stringify({
        success: true,
        reply,
        conversation: {
          token,
          status: reply.actions.some((action) => action.type === 'open_lead_form') ? 'lead_pending' : 'active',
          messages: [welcomeMessage, { role: 'visitor', content, type: 'answer' }, reply],
          leadSubmitted: false,
          requestLead: reply.actions.some((action) => action.type === 'open_lead_form'),
          qualification: reply.qualification,
          contact: null,
        },
      }),
    });
  });
});

test('DORA affiche un CTA visible avant les sources et ouvre un formulaire prérempli sans envoi automatique', async ({ page }) => {
  let leadRequests = 0;
  await page.route(`**/api/chat/conversations/${token}/lead`, async (route) => {
    leadRequests += 1;
    await route.fulfill({ status: 500, body: 'unexpected submit' });
  });

  await page.goto('/');
  await page.locator('#oling-chat-widget').evaluate((root) => root.querySelector('.oling-chat-widget__launcher').click());
  await page.locator('textarea[name="chatMessage"]').fill('Bonjour, je suis une société de gestion des actifs financiers. Nous devons être conformes à DORA. Avez-vous cette expérience ?');
  const responsePromise = page.waitForResponse(`**/api/chat/conversations/${token}/messages`);
  await page.locator('.oling-chat-widget__send').click();
  const response = await responsePromise;
  const body = await response.json();

  expect(body.reply.type).toBe('contact_offer');
  expect(body.reply.actions).toContainEqual({ type: 'open_lead_form', label: 'Être recontacté par OLING' });

  const widget = page.locator('#oling-chat-widget');
  const cta = widget.locator('.oling-chat-widget__assistant-action', { hasText: 'Être recontacté par OLING' });
  const sources = widget.locator('.oling-chat-widget__sources-inline').last();
  await expect(widget).toContainText('premier échange avec un consultant OLING');
  await expect(cta).toBeVisible();
  await expect(sources).toBeVisible();

  const ctaBox = await cta.boundingBox();
  const sourcesBox = await sources.boundingBox();
  expect(ctaBox.y).toBeLessThan(sourcesBox.y);

  await cta.click();
  const form = page.locator('[data-chat-lead]');
  await expect(form).toBeVisible();
  const description = form.locator('textarea[name="chatNeedDescription"]');
  await expect(description).toHaveValue(/DORA \/ résilience opérationnelle numérique/);
  await expect(description).toHaveValue(/société de gestion d’actifs financiers/);
  await expect(description).toHaveValue(/accompagnement à la conformité DORA/);
  await expect(description).toHaveValue(/premier échange avec un consultant OLING/);
  await expect(form.locator('input[name="chatPhone"]')).toHaveValue('');
  await expect(form.locator('input[name="chatConsent"]')).not.toBeChecked();
  expect(leadRequests).toBe(0);
});

test('DPO et SI Finance proposent un contact, les demandes pédagogiques ne le font pas', async ({ page }) => {
  await page.goto('/');
  await page.locator('#oling-chat-widget').evaluate((root) => root.querySelector('.oling-chat-widget__launcher').click());

  const sendAndExpectButton = async (message) => {
    await page.locator('textarea[name="chatMessage"]').fill(message);
    await page.locator('.oling-chat-widget__send').click();
    await expect(page.locator('#oling-chat-widget .oling-chat-widget__assistant-action', { hasText: 'Être recontacté par OLING' }).last()).toBeVisible();
  };

  await sendAndExpectButton('Nous recherchons un DPO externalisé pour notre organisation.');
  await sendAndExpectButton('Nous voulons cadrer notre SI Finance et nos flux de reporting.');

  await page.evaluate(() => window.localStorage.clear());
  await page.reload();
  await page.locator('#oling-chat-widget').evaluate((root) => root.querySelector('.oling-chat-widget__launcher').click());
  await page.locator('textarea[name="chatMessage"]').fill('DORA, c’est quoi ?');
  await page.locator('.oling-chat-widget__send').click();
  await expect(page.locator('#oling-chat-widget .oling-chat-widget__assistant-action')).toHaveCount(0);

  await page.evaluate(() => window.localStorage.clear());
  await page.reload();
  await page.locator('#oling-chat-widget').evaluate((root) => root.querySelector('.oling-chat-widget__launcher').click());
  await page.locator('textarea[name="chatMessage"]').fill('Je suis étudiant, pouvez-vous me définir l’AMOA ?');
  await page.locator('.oling-chat-widget__send').click();
  await expect(page.locator('#oling-chat-widget .oling-chat-widget__assistant-action')).toHaveCount(0);
});

for (const viewport of [
  { name: 'desktop', size: { width: 1440, height: 900 } },
  { name: 'mobile', size: { width: 390, height: 844 } },
]) {
  test(`Demande de proposition AMOA ERP ouvre le formulaire prérempli (${viewport.name})`, async ({ page }) => {
    await page.setViewportSize(viewport.size);
    await page.goto('/');
    await page.locator('#oling-chat-widget').evaluate((root) => root.querySelector('.oling-chat-widget__launcher').click());
    await page.locator('textarea[name="chatMessage"]').fill(erpProposalMessage);
    await page.locator('.oling-chat-widget__send').click();

    const cta = page.locator('#oling-chat-widget .oling-chat-widget__assistant-action', { hasText: 'Recevoir une proposition OLING' });
    await expect(cta).toBeVisible();
    await cta.click();
    await expectErpProposalPrefill(page);
  });
}

test('OpenAI indisponible conserve la demande de proposition et ouvre le formulaire', async ({ page }) => {
  await page.route(`**/api/chat/conversations/${token}/messages`, async (route) => {
    const payload = route.request().postDataJSON();
    const content = payload.content || '';
    await route.fulfill({
      contentType: 'application/json',
      body: JSON.stringify({
        success: true,
        reply: replies.erpUnavailable,
        conversation: {
          token,
          status: 'lead_pending',
          messages: [welcomeMessage, { role: 'visitor', content, type: 'answer' }, replies.erpUnavailable],
          leadSubmitted: false,
          requestLead: true,
          qualification: replies.erpUnavailable.qualification,
          contact: null,
        },
      }),
    });
  });

  await page.goto('/');
  await page.locator('#oling-chat-widget').evaluate((root) => root.querySelector('.oling-chat-widget__launcher').click());
  await page.locator('textarea[name="chatMessage"]').fill(erpProposalMessage);
  await page.locator('.oling-chat-widget__send').click();
  const cta = page.locator('#oling-chat-widget .oling-chat-widget__assistant-action', { hasText: 'Transmettre ma demande de proposition à OLING' });
  await expect(cta).toBeVisible();
  await cta.click();
  await expectErpProposalPrefill(page);
});

test('Backend indisponible affiche une continuité locale sans accusé d’envoi', async ({ page }) => {
  await page.unroute(`**/api/chat/conversations/${token}/messages`);
  await page.route(`**/api/chat/conversations/${token}/messages`, async (route) => {
    await route.abort('failed');
  });

  await page.goto('/');
  await page.locator('#oling-chat-widget').evaluate((root) => root.querySelector('.oling-chat-widget__launcher').click());
  await page.locator('textarea[name="chatMessage"]').fill(erpProposalMessage);
  await page.locator('.oling-chat-widget__send').click();

  await expect(page.locator('#oling-chat-widget')).toContainText('difficulté technique');
  await expect(page.locator('#oling-chat-widget .oling-chat-widget__assistant-action', { hasText: 'Copier ma demande' })).toBeVisible();
  await expect(page.locator('#oling-chat-widget')).not.toContainText('votre demande a bien été envoyée');
});

test('Question ERP informative ne déclenche pas de proposition', async ({ page }) => {
  await page.goto('/');
  await page.locator('#oling-chat-widget').evaluate((root) => root.querySelector('.oling-chat-widget__launcher').click());
  await page.locator('textarea[name="chatMessage"]').fill('Qu’est-ce qu’une AMOA ERP ?');
  await page.locator('.oling-chat-widget__send').click();
  await expect(page.locator('#oling-chat-widget .oling-chat-widget__assistant-action')).toHaveCount(0);
});
