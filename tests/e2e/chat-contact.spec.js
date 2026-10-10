const { test, expect } = require('@playwright/test');

const token = 'test-chat-token';

const welcomeMessage = {
  role: 'assistant',
  content: 'Bonjour. Je suis l’assistant expert OLING.',
  type: 'welcome',
  sources: [],
  sourceCards: [],
  actions: [],
};

const assistantMessage = {
  role: 'assistant',
  type: 'lead_request',
  status: 'llm_primary',
  content: 'Pour Maison&Objet, le besoin ressemble à une transformation SI commercial avec remplacement CRM, architecture cible à clarifier et AMOA indépendante. Vous pouvez contacter OLING au 01 89 70 15 60, écrire à [contact@oling.fr](mailto:contact@oling.fr) ou utiliser le formulaire /contact?chat_fallback=1.',
  sources: [],
  sourceCards: [],
  actions: [{ type: 'open_lead_form', label: 'Transmettre mon projet à OLING' }],
};

const qualification = {
  primary_need: 'crm',
  urgency_level: 'planned',
  maturity_level: 'cadre',
  organization_type: 'eti',
  organization_size: 'unknown',
  commercial_intent: 'cadrage',
  potential_value: 'high',
};

test.beforeEach(async ({ page }) => {
  await page.addInitScript(() => {
    window.localStorage.clear();
  });

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
    await route.fulfill({
      contentType: 'application/json',
      body: JSON.stringify({
        success: true,
        reply: assistantMessage,
        conversation: {
          token,
          status: 'lead_pending',
          messages: [
            welcomeMessage,
            {
              role: 'visitor',
              content: 'Maison&Objet veut remplacer son CRM, structurer une architecture cible et sécuriser une AMOA indépendante.',
              type: 'answer',
            },
            assistantMessage,
          ],
          leadSubmitted: false,
          requestLead: true,
          qualification,
          contact: null,
        },
      }),
    });
  });

  await page.route(`**/api/chat/conversations/${token}/lead`, async (route) => {
    const payload = route.request().postDataJSON();
    expect(payload.phone).toBe('');
    expect(payload.needDescription).toContain('remplacer son CRM');
    expect(payload.needDescription).toContain('AMOA indépendante');
    expect(payload.needDescription).toContain('Expertise pressentie : crm');

    await route.fulfill({
      contentType: 'application/json',
      body: JSON.stringify({
        success: true,
        message: 'Merci, votre demande a bien été envoyée.',
        qualification,
        conversation: {
          token,
          status: 'submitted',
          summaryShort: 'Demande envoyée.',
          messages: [welcomeMessage, assistantMessage],
          leadSubmitted: true,
          requestLead: false,
          qualification,
          contact: {
            fullName: payload.fullName,
            email: payload.email,
            phone: payload.phone,
            company: payload.company,
          },
        },
      }),
    });
  });
});

test('Maison&Objet peut transmettre son projet sans ressaisie', async ({ page }) => {
  await page.goto('/');
  await page.locator('#oling-chat-widget').evaluate((root) => root.querySelector('.oling-chat-widget__launcher').click());
  await expect(page.locator('#oling-chat-widget')).toHaveClass(/is-open/);

  const input = page.locator('textarea[name="chatMessage"]');
  await input.fill('Maison&Objet veut remplacer son CRM, structurer une architecture cible et sécuriser une AMOA indépendante.');
  await page.locator('.oling-chat-widget__send').click();

  await expect(page.locator('#oling-chat-widget')).toContainText('transformation SI commercial');
  const actionButton = page.locator('.oling-chat-widget__assistant-action', { hasText: 'Transmettre mon projet à OLING' });
  await expect(actionButton).toBeVisible();
  await expect(page.locator('#oling-chat-widget')).not.toContainText('/contact?chat_fallback=1');
  await expect(page.locator('#oling-chat-widget a[href="tel:0189701560"]')).toBeVisible();
  await expect(page.locator('#oling-chat-widget a[href="mailto:contact@oling.fr"]')).toBeVisible();

  await actionButton.click();

  const leadForm = page.locator('[data-chat-lead]');
  await expect(leadForm).toBeVisible();
  await expect(leadForm.locator('textarea[name="chatNeedDescription"]')).toHaveValue(/remplacer son CRM/);
  await expect(leadForm.locator('textarea[name="chatNeedDescription"]')).toHaveValue(/AMOA indépendante/);

  await leadForm.locator('input[name="chatFullName"]').fill('Camille Martin');
  await leadForm.locator('input[name="chatEmail"]').fill('camille.martin@example.com');
  await leadForm.locator('input[name="chatCompany"]').fill('Maison&Objet');
  await leadForm.locator('input[name="chatConsent"]').check();
  await leadForm.getByRole('button', { name: 'Transmettre mon projet à OLING' }).click();

  await expect(page.locator('[data-chat-summary]')).toContainText('Demande envoyée');
});
