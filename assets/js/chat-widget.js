const CHAT_STORAGE_KEY = 'oling_chat_conversation_token';
const CHAT_OPEN_STATE_KEY = 'oling_chat_open_state';
const CHAT_STORAGE_VERSION_KEY = 'oling_chat_storage_version';
let CHAT_I18N = {};

const parseJson = async (response) => {
  const text = await response.text();
  try {
    return text ? JSON.parse(text) : {};
  } catch (error) {
    return {};
  }
};

const escapeHtml = (value) => {
  const div = document.createElement('div');
  div.textContent = value || '';
  return div.innerHTML;
};

const getLinkAttributes = (url) => {
  try {
    const parsed = new URL(url, window.location.origin);
    const isInternal = parsed.origin === window.location.origin;
    const isHttp = parsed.protocol === 'http:' || parsed.protocol === 'https:';

    if (!isHttp || isInternal) {
      return 'data-chat-bypass="true"';
    }

    return 'target="_blank" rel="noopener" data-chat-bypass="true"';
  } catch (error) {
    return 'data-chat-bypass="true"';
  }
};

const sourceLabel = (url) => {
  try {
    const parsed = new URL(url, window.location.origin);
    return parsed.pathname
      .split('/')
      .filter(Boolean)
      .pop()
      ?.replace(/[-_]+/g, ' ') || (CHAT_I18N.sourceResource || 'Ressource');
  } catch (error) {
    return CHAT_I18N.sourceResource || 'Ressource';
  }
};

const formatMessageContent = (value) => escapeHtml(value).replace(/\n/g, '<br>');

const analyticsEventMap = {
  open_lead_form: 'lead_form_opened',
  start_diagnostic: 'diagnostic_started',
  generate_scoping_note: 'scoping_note_generated',
  download_scoping_note: 'scoping_note_downloaded',
};

const sanitizeAssistantIntro = (value) => String(value || '')
  .replace('Posez une question sur OLING.', '')
  .trim();

const escapeHtmlWithBasicInlineMarkup = (value) => {
  let html = escapeHtml(value)
    .replace(/\[([^\]]+)\]\(\/contact\?chat_fallback=1\)/g, '$1')
    .replace(/\/contact\?chat_fallback=1/g, 'le formulaire de contact')
    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
    .replace(/\[([^\]]+)\]\(([^)\s]+)\)/g, (_, label, url) => `<a href="${url}" ${getLinkAttributes(url)}>${label}</a>`);

  html = html
    .replace(/\b01\s?89\s?70\s?15\s?60\b/g, '<a href="tel:0189701560" data-chat-bypass="true">01 89 70 15 60</a>')
    .replace(/(^|[\s(])contact@oling\.fr(?!<\/a>)/g, '$1<a href="mailto:contact@oling.fr" data-chat-bypass="true">contact@oling.fr</a>');

  return html;
};

const renderContactAssistantCard = (lines) => {
  const phoneLine = lines.find((line) => /^-?\s*Téléphone\s*:/i.test(line));
  const emailLine = lines.find((line) => /^-?\s*Email\s*:/i.test(line));
  const formLine = lines.find((line) => /^-?\s*Formulaire\s*:/i.test(line));

  if (!phoneLine || !emailLine) {
    return null;
  }

  const phone = (phoneLine.split(':').slice(1).join(':') || '').trim();
  const email = (emailLine.split(':').slice(1).join(':') || '').trim();
  const formMatch = formLine?.match(/\[([^\]]+)\]\(([^)]+)\)/);
  const intro = lines.find((line) => !/^-\s*(Téléphone|Email|Formulaire)\s*:/i.test(line)) || (CHAT_I18N.contactIntro || 'Si vous souhaitez contacter OLING :');
  const phoneHref = `tel:${phone.replace(/[^+\d]/g, '')}`;
  const isContinuityMessage = /difficulté technique|momentanément indisponible/i.test(intro);

  return `
    <div class="oling-chat-widget__assistant-contact-card">
      <p class="oling-chat-widget__assistant-contact-intro">${escapeHtmlWithBasicInlineMarkup(intro)}</p>
      <div class="oling-chat-widget__assistant-contact-links">
        ${isContinuityMessage ? `<button type="button" class="oling-chat-widget__assistant-contact-link" data-chat-retry-last>${escapeHtml(CHAT_I18N.retryAi || 'Réessayer avec l’IA')}</button>` : ''}
        <a class="oling-chat-widget__assistant-contact-link oling-chat-widget__assistant-contact-link--phone" href="${escapeHtml(phoneHref)}" data-chat-bypass="true">${escapeHtml(phone)}</a>
        <a class="oling-chat-widget__assistant-contact-link oling-chat-widget__assistant-contact-link--email" href="mailto:${escapeHtml(email)}" data-chat-bypass="true">${escapeHtml(email)}</a>
      </div>
      <button type="button" class="oling-chat-widget__assistant-contact-form-link" data-chat-open-lead>${escapeHtml(CHAT_I18N.transmitProject || 'Transmettre mon projet à OLING')}</button>
    </div>
  `;
};

const formatAssistantContent = (value) => {
  const lines = sanitizeAssistantIntro(value)
    .split('\n')
    .map((line) => line.trim())
    .filter(Boolean);

  if (!lines.length) {
    return '';
  }

  const contactCardHtml = renderContactAssistantCard(lines);
  if (contactCardHtml) {
    return contactCardHtml;
  }

  const blocks = [];
  let listBuffer = [];
  let listType = 'ul';

  const flushList = () => {
    if (!listBuffer.length) return;
    blocks.push(`
      <${listType} class="oling-chat-widget__assistant-list">
        ${listBuffer.map((item) => `<li>${escapeHtmlWithBasicInlineMarkup(item)}</li>`).join('')}
      </${listType}>
    `);
    listBuffer = [];
    listType = 'ul';
  };

  lines.forEach((line) => {
    const bulletMatch = line.match(/^[-•]\s+(.+)$/);
    const numberedMatch = line.match(/^\d+[.)]\s+(.+)$/);
    const headingMatch = line.match(/^([^:]{2,80})\s*:\s*$/);
    const markdownHeadingMatch = line.match(/^#{1,3}\s+(.+)$/);

    if (markdownHeadingMatch) {
      flushList();
      blocks.push(`<p class="oling-chat-widget__assistant-heading">${escapeHtmlWithBasicInlineMarkup(markdownHeadingMatch[1])}</p>`);
      return;
    }

    if (bulletMatch || numberedMatch) {
      const nextType = numberedMatch ? 'ol' : 'ul';
      if (listBuffer.length && listType !== nextType) {
        flushList();
      }
      listType = nextType;
      listBuffer.push((bulletMatch || numberedMatch)[1]);
      return;
    }

    if (headingMatch) {
      flushList();
      blocks.push(`<p class="oling-chat-widget__assistant-heading">${escapeHtmlWithBasicInlineMarkup(headingMatch[1])}</p>`);
      return;
    }

    flushList();
    blocks.push(`<p>${escapeHtmlWithBasicInlineMarkup(line)}</p>`);
  });

  flushList();
  return blocks.join('');
};

const sourceTypeLabel = (type) => {
  const labels = {
    page: CHAT_I18N.sourcePage || 'Page',
    expertise: CHAT_I18N.sourceExpertise || 'Expertise',
    service: CHAT_I18N.sourceService || 'Service',
    reference: CHAT_I18N.sourceReference || 'Référence OLING',
    team: CHAT_I18N.sourceTeam || 'Équipe',
  };

  return labels[type] || CHAT_I18N.sourceResource || 'Ressource';
};

const getMessageSourceCards = (message) => (
  message.sourceCards && message.sourceCards.length
    ? message.sourceCards
    : (message.sources || []).map((url) => ({
        url,
        title: sourceLabel(url),
        type: 'page',
        typeLabel: CHAT_I18N.sourceResource || 'Ressource',
        image: null,
        excerpt: '',
      }))
);

const sourceExcerpt = (card) => {
  const excerpt = String(card.excerpt || '').trim();
  if (!excerpt) return '';
  const title = String(card.title || '').trim().toLowerCase();
  const compactExcerpt = excerpt.toLowerCase();
  if (title && (compactExcerpt.includes(title) || title.includes(compactExcerpt))) return '';
  return excerpt.length > 118 ? `${excerpt.slice(0, 115).trim()}...` : excerpt;
};

const createSourceCardsHtml = (cards) => `
  <div class="oling-chat-widget__sources-inline" aria-label="${escapeHtml(CHAT_I18N.sourcesUseful || 'Sources utiles')}">
    <div class="oling-chat-widget__sources-inline-title">${escapeHtml(CHAT_I18N.sourcesUseful || 'Sources utiles')}</div>
    ${cards.map((card) => `
      <a class="oling-chat-widget__source-card oling-chat-widget__source-card--inline" href="${escapeHtml(card.url)}" ${getLinkAttributes(card.url)}>
        <span class="oling-chat-widget__source-body">
          <span class="oling-chat-widget__source-type">${escapeHtml(card.typeLabel || sourceTypeLabel(card.type))}</span>
          <span class="oling-chat-widget__source-title">${escapeHtml(card.title || sourceLabel(card.url))}</span>
          ${sourceExcerpt(card) ? `<span class="oling-chat-widget__source-excerpt">${escapeHtml(sourceExcerpt(card))}</span>` : ''}
        </span>
      </a>
    `).join('')}
  </div>
`;

const createActionButtonsHtml = (actions = [], content = '') => {
  const supportedTypes = ['open_lead_form', 'start_diagnostic', 'generate_scoping_note', 'download_scoping_note', 'copy_request'];
  const hasDirectContactDetails = /Téléphone\s*:|Email\s*:/i.test(String(content || ''));
  const usableActions = actions
    .filter((action) => supportedTypes.includes(action.type))
    .filter((action) => !(hasDirectContactDetails && action.type === 'open_lead_form'));
  if (!usableActions.length) return '';

  return `
    <div class="oling-chat-widget__assistant-actions">
      ${usableActions.map((action) => `<button type="button" class="oling-chat-widget__assistant-action" data-chat-action="${escapeHtml(action.type)}">${escapeHtml(action.label || 'Transmettre mon projet à OLING')}</button>`).join('')}
    </div>
  `;
};

const createMessageHtml = (message) => `
  <article class="oling-chat-widget__message oling-chat-widget__message--${message.role}">
    ${message.role === 'assistant'
      ? `
        <div class="oling-chat-widget__assistant-block">
          <div class="oling-chat-widget__message-meta">${escapeHtml(CHAT_I18N.assistantMeta || 'OLING')}</div>
          <div class="oling-chat-widget__assistant-body">${formatAssistantContent(message.content)}</div>
          ${createActionButtonsHtml(message.actions || [], message.content)}
        </div>
      `
      : `
        <div class="oling-chat-widget__message-meta">${escapeHtml(CHAT_I18N.visitorMeta || 'Vous')}</div>
        <div class="oling-chat-widget__bubble">${formatMessageContent(message.content)}</div>
      `}
    ${message.role === 'assistant' && getMessageSourceCards(message).length ? createSourceCardsHtml(getMessageSourceCards(message)) : ''}
  </article>
`;

const createTypingHtml = () => '';

const createWelcomeHtml = () => `
  <div class="oling-chat-widget__welcome">
    <div class="oling-chat-widget__welcome-badge">${escapeHtml(CHAT_I18N.welcomeBadge || 'Assistant expert IA')}</div>
    <p>${escapeHtml(CHAT_I18N.welcomeText || 'L’assistant peut vous orienter sur les expertises, les expériences, l’équipe et les démarches d’accompagnement.')}</p>
    <ul class="oling-chat-widget__welcome-list">
      <li>${escapeHtml(CHAT_I18N.welcomeItem1 || 'Expertises SI, ERP, GMAO, conformité, data ou cybersécurité')}</li>
      <li>${escapeHtml(CHAT_I18N.welcomeItem2 || 'Références anonymisées par secteur, mission ou technologie')}</li>
      <li>${escapeHtml(CHAT_I18N.welcomeItem3 || 'Profils OLING pertinents selon votre sujet')}</li>
    </ul>
  </div>
`;

const initChatWidget = () => {
  const root = document.getElementById('oling-chat-widget');
  if (!root) return;
  try {
    CHAT_I18N = JSON.parse(root.querySelector('[data-chat-i18n]')?.textContent || '{}');
  } catch (error) {
    CHAT_I18N = {};
  }

  const launcher = root.querySelector('.oling-chat-widget__launcher');
  const panel = root.querySelector('.oling-chat-widget__panel');
  const body = root.querySelector('.oling-chat-widget__body');
  const closeButton = root.querySelector('.oling-chat-widget__close');
  const messages = root.querySelector('[data-chat-messages]');
  const leadBlock = root.querySelector('[data-chat-lead]');
  const erpForm = root.querySelector('[data-chat-erp-form]');
  const erpResult = root.querySelector('[data-chat-erp-result]');
  const errorBox = root.querySelector('[data-chat-error]');
  const statusBox = root.querySelector('[data-chat-status]');
  const summaryBox = root.querySelector('[data-chat-summary]');
  const contactCard = root.querySelector('[data-chat-contact-card]');
  const composer = root.querySelector('[data-chat-composer]');
  const composerShell = composer?.querySelector('.oling-chat-widget__composer-shell');
  const messageInput = composer?.querySelector('textarea[name="chatMessage"]');
  const leadButton = root.querySelector('[data-chat-submit-lead]');
  const submitButton = composer?.querySelector('button[type="submit"]');
  const resetButton = root.querySelector('[data-chat-reset]');
  const contactButton = root.querySelector('.oling-chat-widget__composer-tools a[data-chat-bypass="true"]');
  const contactPath = new URL(root.dataset.contactFallbackUrl, window.location.origin).pathname;
  const defaultPlaceholder = messageInput?.getAttribute('placeholder') || '';
  const defaultLeadLabel = leadButton?.textContent || (CHAT_I18N.submitLead || 'Transmettre la demande');
  const minComposerRows = 1;
  const maxComposerRows = 5;
  const mobileBreakpoint = window.matchMedia('(max-width: 767px)');

  const state = {
    token: window.localStorage.getItem(CHAT_STORAGE_KEY),
    open: window.localStorage.getItem(CHAT_OPEN_STATE_KEY) === 'open',
    loading: false,
    conversation: null,
    typing: false,
    scrollMode: 'bottom',
    lastFailedContent: '',
    firstQuestionTracked: false,
    lastProjectContext: '',
  };

  const trackChatEvent = (name, details = {}) => {
    try {
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({
        event: `oling_chat_${name}`,
        status: details.status || undefined,
        messageType: details.messageType || undefined,
        primaryNeed: details.primaryNeed || undefined,
        commercialIntent: details.commercialIntent || undefined,
      });
    } catch (error) {
      // Analytics must never block the chat.
    }
  };

  const storageVersion = root.dataset.storageVersion || '1';
  if (window.localStorage.getItem(CHAT_STORAGE_VERSION_KEY) !== storageVersion) {
    window.localStorage.removeItem(CHAT_STORAGE_KEY);
    window.localStorage.setItem(CHAT_STORAGE_VERSION_KEY, storageVersion);
    state.token = null;
  }

  const focusMessageInput = () => {
    if (!messageInput || state.loading) return;

    window.requestAnimationFrame(() => {
      messageInput.focus({ preventScroll: true });
      const length = messageInput.value.length;
      messageInput.setSelectionRange(length, length);
      resizeMessageInput();
    });
  };

  const setOpen = (open) => {
    const wasOpen = state.open;
    state.open = open;
    window.localStorage.setItem(CHAT_OPEN_STATE_KEY, open ? 'open' : 'closed');
    root.classList.toggle('is-open', open);
    root.classList.toggle('is-closed', !open);
    document.body.classList.toggle('has-open-chat-widget', open);
    launcher?.setAttribute('aria-expanded', open ? 'true' : 'false');
    panel?.setAttribute('aria-hidden', open ? 'false' : 'true');
    if (open) {
      if (!wasOpen) {
        trackChatEvent('opened');
        trackChatEvent('chat_opened');
      }
      updateViewportHeight();
      focusMessageInput();
      scrollMessagesToBottom();
    }
  };

  const updateViewportHeight = () => {
    const viewportHeight = window.visualViewport?.height || window.innerHeight;
    document.documentElement.style.setProperty('--oling-chat-viewport-height', `${viewportHeight}px`);
  };

  const setError = (message = '') => {
    if (!errorBox) return;
    errorBox.textContent = message;
    errorBox.classList.toggle('d-none', !message);
  };

  const setStatus = (message = '') => {
    if (!statusBox) return;
    statusBox.textContent = message;
    statusBox.classList.toggle('d-none', !message);
  };

  const setSummary = (message = '', tone = 'info') => {
    if (!summaryBox) return;
    summaryBox.textContent = message;
    summaryBox.dataset.tone = tone;
    summaryBox.classList.toggle('d-none', !message);
  };

  const syncContactCard = () => {
    if (!contactCard) return;
    contactCard.classList.add('d-none');
  };

  const setLoading = (loading, message = '') => {
    state.loading = loading;
    if (!loading) {
      state.typing = false;
    }
    root.classList.toggle('is-loading', loading);
    messageInput?.toggleAttribute('disabled', loading);
    leadButton?.toggleAttribute('disabled', loading);
    submitButton?.toggleAttribute('disabled', loading);
    resetButton?.toggleAttribute('disabled', loading);
    launcher?.toggleAttribute('disabled', loading && !state.open);
    submitButton?.setAttribute('aria-label', loading ? (CHAT_I18N.sending || 'Envoi en cours') : (CHAT_I18N.send || 'Envoyer'));
    submitButton?.setAttribute('title', loading ? (CHAT_I18N.sending || 'Envoi en cours') : (CHAT_I18N.send || 'Envoyer'));
    if (leadButton) {
      leadButton.textContent = loading ? `${CHAT_I18N.sending || 'En cours'}...` : defaultLeadLabel;
    }
    setStatus(loading ? message : '');
    if (!loading && state.conversation) {
      renderMessageList(state.conversation.messages || []);
    }
  };

  const setLeadVisible = (visible) => {
    if (!leadBlock) return;
    leadBlock.classList.toggle('d-none', !visible);
    root.classList.toggle('is-lead-step', visible);
  };

  const resizeMessageInput = () => {
    if (!messageInput) return;

    const computedStyle = window.getComputedStyle(messageInput);
    const lineHeight = parseFloat(computedStyle.lineHeight) || 24;
    const paddingTop = parseFloat(computedStyle.paddingTop) || 0;
    const paddingBottom = parseFloat(computedStyle.paddingBottom) || 0;
    const borderTop = parseFloat(computedStyle.borderTopWidth) || 0;
    const borderBottom = parseFloat(computedStyle.borderBottomWidth) || 0;
    const verticalExtra = paddingTop + paddingBottom + borderTop + borderBottom;
    const minHeight = lineHeight * minComposerRows + verticalExtra;
    const maxHeight = lineHeight * maxComposerRows + verticalExtra;

    messageInput.style.height = 'auto';
    const nextHeight = Math.min(Math.max(messageInput.scrollHeight, minHeight), maxHeight);
    messageInput.style.height = `${nextHeight}px`;
    messageInput.style.overflowY = messageInput.scrollHeight > maxHeight ? 'auto' : 'hidden';
  };

  const scrollMessagesToBottom = () => {
    const scrollContainer = body || messages;
    if (!scrollContainer) return;
    window.requestAnimationFrame(() => {
      scrollContainer.scrollTop = scrollContainer.scrollHeight;
    });
  };

  const scrollToLatestAssistantStart = () => {
    const scrollContainer = body || messages;
    const latestAssistantMessage = messages?.querySelector('.oling-chat-widget__message--assistant:last-of-type');
    if (!scrollContainer || !latestAssistantMessage) return;

    window.requestAnimationFrame(() => {
      const containerRect = scrollContainer.getBoundingClientRect();
      const messageRect = latestAssistantMessage.getBoundingClientRect();
      const nextScrollTop = scrollContainer.scrollTop + (messageRect.top - containerRect.top) - 8;
      scrollContainer.scrollTop = Math.max(0, nextScrollTop);
    });
  };

  const applyScrollMode = () => {
    if (state.scrollMode === 'assistant-start') {
      scrollToLatestAssistantStart();
      return;
    }

    scrollMessagesToBottom();
  };

  const renderMessageList = (messageList = []) => {
    if (!messages) return;

    const hasVisitorMessage = messageList.some((message) => message.role === 'visitor');
    const isInitialHistory = !hasVisitorMessage && messageList.length <= 1;
    root.classList.toggle('is-empty-history', !messageList.length);
    root.classList.toggle('is-initial-history', isInitialHistory);
    messages.innerHTML = messageList.length
      ? messageList.map(createMessageHtml).join('') + (state.loading && state.typing ? createTypingHtml() : '')
      : createWelcomeHtml();
    applyScrollMode();
  };

  const syncResetVisibility = (conversation = state.conversation) => {
    if (!resetButton) return;
    const hasVisitorMessage = (conversation?.messages || []).some((message) => message.role === 'visitor');
    resetButton.classList.toggle('d-none', !hasVisitorMessage);
  };

  const renderConversation = (conversation) => {
    const previousMessageCount = state.conversation?.messages?.length || 0;
    state.conversation = conversation;
    if (!messages) return;

    const messageList = conversation.messages || [];
    const lastMessage = messageList.length ? messageList[messageList.length - 1] : null;

    state.scrollMode = 'bottom';
    if (!lastMessage || lastMessage.role === 'assistant') {
      state.typing = false;
    }
    if (
      lastMessage
      && lastMessage.role === 'assistant'
      && messageList.length > previousMessageCount
    ) {
      state.scrollMode = 'assistant-start';
    }
    syncResetVisibility(conversation);
    syncContactCard(conversation);
    renderMessageList(messageList);
    const qualification = conversation.qualification || {};
    if (qualification.primary_need) {
      trackChatEvent('need_identified', { primaryNeed: qualification.primary_need, commercialIntent: qualification.commercial_intent });
    }
    if (qualification.commercial_intent && qualification.commercial_intent !== 'information') {
      trackChatEvent('conversation_qualified', { primaryNeed: qualification.primary_need, commercialIntent: qualification.commercial_intent });
    }
    if (lastMessage?.role === 'assistant' && lastMessage?.type) {
      trackChatEvent(
        lastMessage.type === 'technical_unavailable' ? 'llm_unavailable' : 'assistant_reply',
        { status: lastMessage.status, messageType: lastMessage.type }
      );
    }
    const hasDirectContactDetails = !!conversation && (conversation.messages || []).some((message) => (
      message.role === 'assistant'
      && /01 89 70 15 60|contact@oling\.fr/i.test(String(message.content || ''))
    ));
    const shouldShowLead = !!conversation.requestLead && !conversation.leadSubmitted && !hasDirectContactDetails;
    setLeadVisible(shouldShowLead);
    if (shouldShowLead) {
      trackChatEvent('cta_presented', { primaryNeed: qualification.primary_need, commercialIntent: qualification.commercial_intent });
      prefillLeadDescription(true);
    }

    if (conversation.contact) {
      root.querySelector('input[name="chatFullName"]').value = conversation.contact.fullName || '';
      root.querySelector('input[name="chatEmail"]').value = conversation.contact.email || '';
      root.querySelector('input[name="chatPhone"]').value = conversation.contact.phone || '';
      root.querySelector('input[name="chatCompany"]').value = conversation.contact.company || '';
    }

    composer?.classList.remove('d-none');

    if (conversation.leadSubmitted) {
      setLeadVisible(false);
      setSummary(
        conversation.summaryShort || (CHAT_I18N.leadSubmitted || 'Demande bien envoyée. Vous pouvez continuer la conversation, ajouter une précision ou poser une autre question.'),
        'success'
      );
      if (messageInput) {
        messageInput.placeholder = CHAT_I18N.followupPlaceholder || 'Ajouter un complément, une précision ou un autre besoin...';
        resizeMessageInput();
      }
      return;
    }

    setSummary(
      conversation.requestLead && !hasDirectContactDetails
        ? (CHAT_I18N.leadOffer || 'Si vous souhaitez être recontacté, vous pouvez laisser vos coordonnées ci-dessous. Vous pouvez aussi continuer à préciser votre besoin.')
        : '',
      'info'
    );
    if (messageInput) {
      messageInput.placeholder = defaultPlaceholder;
       resizeMessageInput();
    }
    if (submitButton) {
      submitButton.setAttribute('aria-label', CHAT_I18N.send || 'Envoyer');
      submitButton.setAttribute('title', CHAT_I18N.send || 'Envoyer');
    }
  };

  const request = async (url, options = {}) => {
    const response = await fetch(url, {
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': root.dataset.csrfToken,
      },
      ...options,
    });

    const payload = await parseJson(response);
    if (!response.ok) {
      throw new Error(payload.message || (CHAT_I18N.genericError || 'Une erreur est survenue.'));
    }

    return payload;
  };

  const showUrl = (token) => root.dataset.showUrlTemplate.replace('CHAT_TOKEN', token);
  const messageUrl = (token) => root.dataset.messageUrlTemplate.replace('CHAT_TOKEN', token);
  const leadUrl = (token) => root.dataset.leadUrlTemplate.replace('CHAT_TOKEN', token);
  const scopingNoteUrl = (token) => root.dataset.scopingNoteUrlTemplate.replace('CHAT_TOKEN', token);
  const erpQuestionnaireUrl = (token) => root.dataset.erpQuestionnaireUrlTemplate.replace('CHAT_TOKEN', token);

  const setErpVisible = (visible) => {
    erpForm?.classList.toggle('d-none', !visible);
    if (visible) {
      setLeadVisible(false);
      erpResult?.classList.add('d-none');
      composer?.classList.add('d-none');
      scrollMessagesToBottom();
      return;
    }
    composer?.classList.remove('d-none');
  };

  const summaryList = (items) => (items || []).map((item) => `<li>${escapeHtml(item)}</li>`).join('');

  const renderErpResult = (summary, pdfUrl) => {
    if (!erpResult) return;
    erpResult.innerHTML = `
      <div class="oling-chat-widget__lead-head">
        <div class="oling-chat-widget__lead-title">${escapeHtml(CHAT_I18N.erpResultTitle || 'Synthèse ERP / progiciel')}</div>
        <p>${escapeHtml(summary.executive_summary || '')}</p>
      </div>
      <p><strong>${escapeHtml(CHAT_I18N.erpMaturity || 'Maturité')} :</strong> ${escapeHtml(summary.maturity || '')}</p>
      <p><strong>${escapeHtml(CHAT_I18N.erpComplexity || 'Complexité')} :</strong> ${escapeHtml(summary.complexity || '')}</p>
      <p><strong>${escapeHtml(CHAT_I18N.erpCharge || 'Charge AMOA')} :</strong> ${escapeHtml(summary.amoa_charge_estimate || '')}</p>
      <p><strong>${escapeHtml(CHAT_I18N.erpBudget || 'Budget AMOA')} :</strong> ${escapeHtml(summary.amoa_budget_estimate || '')}</p>
      <div class="oling-chat-widget__field-label">${escapeHtml(CHAT_I18N.erpDeliverables || 'Livrables recommandés')}</div>
      <ul class="oling-chat-widget__assistant-list">${summaryList(summary.recommended_deliverables)}</ul>
      <div class="oling-chat-widget__field-label">${escapeHtml(CHAT_I18N.erpClarifications || 'Points à clarifier')}</div>
      <ul class="oling-chat-widget__assistant-list">${summaryList(summary.missing_information)}</ul>
      <a class="btn btn-primary w-100" href="${escapeHtml(pdfUrl)}" data-chat-bypass="true">${escapeHtml(CHAT_I18N.erpDownloadPdf || 'Télécharger le PDF')}</a>
      <p class="oling-chat-widget__composer-note mt-2">${escapeHtml(CHAT_I18N.erpNotice || 'À ce stade, les estimations sont indicatives et devront être confirmées après un échange de cadrage avec OLING.')}</p>
    `;
    erpResult.classList.remove('d-none');
    setErpVisible(false);
    composer?.classList.add('d-none');
    scrollMessagesToBottom();
  };

  const collectFormPayload = (form) => {
    const payload = {};
    const data = new FormData(form);
    data.forEach((value, key) => {
      if (key.endsWith('[]')) {
        const cleanKey = key.slice(0, -2);
        payload[cleanKey] = payload[cleanKey] || [];
        payload[cleanKey].push(value);
        return;
      }
      payload[key] = value;
    });
    return payload;
  };

  const createConversation = async () => {
    const payload = await request(root.dataset.createUrl, {
      method: 'POST',
      body: JSON.stringify({
        sourcePath: window.location.pathname,
        sourceUrl: window.location.href,
        referrer: document.referrer || null,
        locale: document.documentElement.lang || 'fr',
      }),
    });

    state.token = payload.token;
    window.localStorage.setItem(CHAT_STORAGE_KEY, payload.token);
    setSummary('');
    renderConversation(payload);
  };

  const restoreConversation = async () => {
    if (!state.token) return false;

    try {
      const payload = await request(showUrl(state.token), { method: 'GET', headers: { 'X-CSRF-TOKEN': root.dataset.csrfToken } });
      renderConversation(payload);
      return true;
    } catch (error) {
      window.localStorage.removeItem(CHAT_STORAGE_KEY);
      state.token = null;
      return false;
    }
  };

  const ensureConversation = async () => {
    if (state.conversation) return;
    const restored = await restoreConversation();
    if (!restored) {
      await createConversation();
    }
  };

  const prefillLeadDescription = (force = false) => {
    const field = root.querySelector('textarea[name="chatNeedDescription"]');
    if (!field || (!force && field.value.trim() !== '') || !state.conversation) return;

    const visitorMessages = (state.conversation.messages || [])
      .filter((message) => message.role === 'visitor')
      .map((message) => message.content);
    const latestScoping = [...(state.conversation.messages || [])]
      .reverse()
      .find((message) => message.role === 'assistant' && ['diagnostic', 'scoping_note'].includes(message.type));
    if (latestScoping?.content) {
      visitorMessages.push(latestScoping.content);
    }
    if (!visitorMessages.length) {
      if (state.lastProjectContext) {
        visitorMessages.push(state.lastProjectContext);
      }
    }
    if (!visitorMessages.length) {
      messages?.querySelectorAll('.oling-chat-widget__message--visitor .oling-chat-widget__bubble').forEach((bubble) => {
        const text = bubble.textContent?.trim();
        if (text) {
          visitorMessages.push(text);
        }
      });
    }

    const qualification = state.conversation.qualification || {};
    const label = (value, fallback = 'à compléter') => value || fallback;
    const sourceUrl = window.location.href;
    const contextText = visitorMessages.join(' / ').trim();
    const normalizedContext = contextText.toLowerCase();
    const projectContextLines = [];

    if (normalizedContext.includes('dora')) {
      projectContextLines.push('Domaine : DORA / résilience opérationnelle numérique');
      projectContextLines.push('Besoin : accompagnement à la conformité DORA');
      projectContextLines.push('Objet : premier échange avec un consultant OLING');
      projectContextLines.push('Contexte : besoin de cadrer le périmètre et la démarche de mise en conformité');
      if (normalizedContext.includes('gestion') && (normalizedContext.includes('actifs') || normalizedContext.includes('asset'))) {
        projectContextLines.push('Secteur : société de gestion d’actifs financiers');
      }
    }
    if (normalizedContext.includes('erp') && (normalizedContext.includes('proposition') || normalizedContext.includes('methodologie') || normalizedContext.includes('méthodologie') || normalizedContext.includes('livrables') || normalizedContext.includes('cout') || normalizedContext.includes('coût'))) {
      projectContextLines.push('Objet : Demande de proposition — AMOA ERP industriel');
      if (normalizedContext.includes('pme') || normalizedContext.includes('40 utilisateur')) {
        projectContextLines.push('Contexte : PME industrielle, environ 40 utilisateurs ERP');
      }
      if (normalizedContext.includes('etudes') || normalizedContext.includes('études') || normalizedContext.includes('achats') || normalizedContext.includes('production')) {
        projectContextLines.push('Processus : études, achats, approvisionnements, stocks, production, qualité, ventes et pilotage');
      }
      projectContextLines.push('Prestations demandées : voir le texte original ci-dessous, à conserver sans omission');
      projectContextLines.push('Attentes commerciales : méthodologie, nombre de jours estimés, livrables, références industrielles et coût');
    }

    field.value = [
      'Objet : demande commerciale issue du chat OLING',
      `Expertise pressentie : ${label(qualification.primary_need)}`,
      `Intention : ${label(qualification.commercial_intent)}`,
      ...projectContextLines,
      `Contexte résumé : ${contextText || 'à compléter'}`,
      `Objectifs : ${label(qualification.primary_need)}`,
      'Périmètre : à compléter',
      `Situation actuelle : ${label(qualification.maturity_level)}`,
      `Échéance / urgence : ${label(qualification.urgency_level)}`,
      'Prochaine étape souhaitée : échange de cadrage avec OLING',
      `Page source : ${sourceUrl}`,
    ].join('\n');
    trackChatEvent('form_prefilled', { primaryNeed: qualification.primary_need, commercialIntent: qualification.commercial_intent });
  };

  const openLeadForm = () => {
    if (!state.conversation || state.conversation.leadSubmitted) return;
    prefillLeadDescription(true);
    setLeadVisible(true);
    setSummary(CHAT_I18N.leadOffer || 'Vérifiez la fiche projet, ajoutez vos coordonnées manquantes puis validez l’envoi.', 'info');
    trackChatEvent('cta_clicked', { primaryNeed: state.conversation.qualification?.primary_need, commercialIntent: state.conversation.qualification?.commercial_intent });
    trackChatEvent('form_opened');
    trackChatEvent('lead_form_opened');
    root.querySelector('input[name="chatFullName"]')?.focus({ preventScroll: true });
    scrollMessagesToBottom();
  };

  const diagnosticPrompt = () => [
    'Je souhaite lancer un mini-diagnostic OLING.',
    'Posez-moi uniquement les questions utiles, une par une si nécessaire, sans me redemander les informations déjà présentes dans notre échange.',
    'À la fin, restituez contexte, irritants, risques, hypothèses, scénarios, recommandations, décisions à prendre et rôle possible d’OLING.',
  ].join(' ');

  const scopingNotePrompt = () => [
    'Préparez une note de cadrage exploitable à partir de notre conversation.',
    'Structure attendue : objet, organisation si connue, contexte, besoins, objectifs, périmètre, risques, scénarios, recommandations, décisions, rôle OLING et prochaines étapes.',
    'Ajoutez une mention indiquant que cette note est indicative, modifiable et à valider avant transmission.',
  ].join(' ');

  const downloadScopingNote = async () => {
    if (!state.token) return;
    const payload = await request(scopingNoteUrl(state.token), {
      method: 'POST',
      body: JSON.stringify({}),
    });
    if (!payload.note?.downloadUrl) {
      throw new Error(CHAT_I18N.genericError || 'Une erreur est survenue.');
    }
    window.location.href = payload.note.downloadUrl;
  };

  const sendStructuredActionMessage = async (content, loadingLabel) => {
    if (state.loading) return;
    setError('');
    setLoading(true, loadingLabel);
    try {
      await ensureConversation();
      renderOptimisticVisitorMessage(content);
      await sendMessage(content);
      state.typing = false;
      setStatus('');
    } catch (error) {
      renderLocalContinuityMessage(content);
      setError('');
    } finally {
      setLoading(false);
    }
  };

  const sendMessage = async (content) => {
    if (!state.token) return;
    const payload = await request(messageUrl(state.token), {
      method: 'POST',
      body: JSON.stringify({
        content,
        sourcePath: window.location.pathname,
        sourceUrl: window.location.href,
      }),
    });

    if (!payload.conversation) {
      throw new Error(CHAT_I18N.genericError || 'Une erreur est survenue.');
    }

    state.typing = false;
    renderConversation(payload.conversation);
    setStatus('');
    prefillLeadDescription();
  };

  const continuityContent = () => CHAT_I18N.continuityMessage || 'Je rencontre momentanément une difficulté technique pour analyser votre demande. Vous pouvez néanmoins contacter directement notre équipe OLING au 01 89 70 15 60 ou à contact@oling.fr.\n- Téléphone : 01 89 70 15 60\n- Email : contact@oling.fr\n- Formulaire : utilisez le bouton « Transmettre mon projet à OLING » lorsqu’il est proposé.';

  const renderLocalContinuityMessage = (content) => {
    state.lastFailedContent = content || state.lastFailedContent;
    const baseConversation = state.conversation || { messages: [] };
    const existingMessages = baseConversation.messages || [];
    const hasVisitor = existingMessages.some((message) => message.role === 'visitor' && message.content === content);
    const nextMessages = [
      ...existingMessages,
      ...(content && !hasVisitor ? [{ role: 'visitor', content }] : []),
      {
        role: 'assistant',
        type: 'technical_unavailable',
        content: continuityContent(),
        sources: [],
        sourceCards: [],
        actions: [{ type: 'copy_request', label: 'Copier ma demande' }],
      },
    ];

    state.typing = false;
    renderConversation({
      ...baseConversation,
      messages: nextMessages,
      requestLead: false,
    });
    setStatus('');
  };

  const openAndSendPrefill = async (content) => {
    if (!content || state.loading) return;
    setOpen(true);
    setError('');
    setLoading(true, CHAT_I18N.openChat || 'Ouverture du chat...');
    try {
      await ensureConversation();
      state.lastProjectContext = content;
      renderOptimisticVisitorMessage(content);
      await sendMessage(content);
      prefillLeadDescription();
    } catch (error) {
      renderLocalContinuityMessage(content);
      setError('');
    } finally {
      setLoading(false);
    }
  };

  const openErpQuestionnaire = async () => {
    if (state.loading) return;
    setOpen(true);
    setError('');
    setLoading(true, CHAT_I18N.loadingOpenErp || 'Ouverture du questionnaire ERP...');
    try {
      await ensureConversation();
      setErpVisible(true);
    } catch (error) {
      setError(error.message || (CHAT_I18N.errorOpenErp || 'Impossible d’ouvrir le questionnaire ERP.'));
    } finally {
      setLoading(false);
    }
  };

  const submitErpQuestionnaire = async () => {
    if (!state.token || !erpForm) return;
    const payload = await request(erpQuestionnaireUrl(state.token), {
      method: 'POST',
      body: JSON.stringify(collectFormPayload(erpForm)),
    });
    renderConversation(payload.conversation);
    renderErpResult(payload.summary || {}, payload.pdfUrl || '');
    setSummary(payload.message || (CHAT_I18N.erpSent || 'Qualification ERP transmise.'), 'success');
  };

  const renderOptimisticVisitorMessage = (content) => {
    const optimisticConversation = {
      ...(state.conversation || {}),
      messages: [...(state.conversation?.messages || []), { role: 'visitor', content }],
    };

    state.typing = true;
    renderConversation(optimisticConversation);
    setStatus(CHAT_I18N.typing || 'OLING rédige sa réponse...');
  };

  const resetConversation = async () => {
    window.localStorage.removeItem(CHAT_STORAGE_KEY);
    state.token = null;
    state.conversation = null;
    setError('');
    setSummary('');
    setStatus('');
    setLeadVisible(false);
    syncContactCard(null);
    syncResetVisibility(null);
    if (messages) {
      messages.innerHTML = createWelcomeHtml();
      scrollMessagesToBottom();
    }
    if (messageInput) {
      messageInput.value = '';
      messageInput.placeholder = defaultPlaceholder;
      resizeMessageInput();
    }
    root.querySelector('input[name="chatFullName"]').value = '';
    root.querySelector('input[name="chatEmail"]').value = '';
    root.querySelector('input[name="chatPhone"]').value = '';
    root.querySelector('input[name="chatCompany"]').value = '';
    root.querySelector('textarea[name="chatNeedDescription"]').value = '';
    root.querySelector('input[name="chatConsent"]').checked = false;
    await createConversation();
  };

  const submitLead = async () => {
    if (!state.token) return;

    const payload = {
      fullName: root.querySelector('input[name="chatFullName"]').value.trim(),
      email: root.querySelector('input[name="chatEmail"]').value.trim(),
      phone: root.querySelector('input[name="chatPhone"]').value.trim(),
      company: root.querySelector('input[name="chatCompany"]').value.trim(),
      needDescription: root.querySelector('textarea[name="chatNeedDescription"]').value.trim(),
      rgpdConsent: root.querySelector('input[name="chatConsent"]').checked,
    };

    const response = await request(leadUrl(state.token), {
      method: 'POST',
      body: JSON.stringify(payload),
    });

    trackChatEvent('lead_confirmed');
    trackChatEvent('lead_submission_confirmed');
    renderConversation(response.conversation);
    setError('');
  };

  launcher?.addEventListener('click', async () => {
    setOpen(true);
    setError('');
    state.typing = false;
    setLoading(true, CHAT_I18N.openChat || 'Ouverture du chat...');
    try {
      await ensureConversation();
      prefillLeadDescription();
      trackChatEvent('form_opened');
      trackChatEvent('chat_opened');
    } catch (error) {
      setError(error.message || (CHAT_I18N.openChatError || 'Impossible d’ouvrir le chat.'));
    } finally {
      setLoading(false);
    }
  });

  closeButton?.addEventListener('click', () => setOpen(false));

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape' || !state.open) return;
    setOpen(false);
    launcher?.focus({ preventScroll: true });
  });

  messageInput?.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter' || event.shiftKey) {
      return;
    }

    event.preventDefault();
    if (!state.loading) {
      composer?.requestSubmit();
    }
  });

  messageInput?.addEventListener('input', () => {
    resizeMessageInput();
  });

  messageInput?.addEventListener('focus', () => {
    root.classList.add('is-composer-focus');
    updateViewportHeight();
  });

  messageInput?.addEventListener('blur', () => {
    window.setTimeout(() => {
      if (document.activeElement !== messageInput) {
        root.classList.remove('is-composer-focus');
        updateViewportHeight();
      }
    }, 120);
  });

  composerShell?.addEventListener('click', (event) => {
    if (!mobileBreakpoint.matches) return;
    if (event.target.closest('button, a')) return;
    focusMessageInput();
  });

  resizeMessageInput();

  composer?.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (state.loading) return;

    const content = messageInput?.value.trim() || '';
    if (!content) return;

    setError('');
    if (!state.firstQuestionTracked) {
      state.firstQuestionTracked = true;
      trackChatEvent('first_question');
    }
    state.lastProjectContext = content;
    messageInput.value = '';
    resizeMessageInput();
    setLoading(true, `${CHAT_I18N.sending || 'Envoi en cours'}...`);
    try {
      await ensureConversation();
      renderOptimisticVisitorMessage(content);
      await sendMessage(content);
      state.typing = false;
      setStatus('');
    } catch (error) {
      renderLocalContinuityMessage(content);
      setError('');
    } finally {
      setLoading(false);
    }
  });

  root.addEventListener('click', async (event) => {
    const actionButton = event.target.closest('[data-chat-action]');
    if (actionButton) {
      event.preventDefault();
      const actionType = actionButton.dataset.chatAction;
      trackChatEvent(analyticsEventMap[actionType] || 'chat_cta_clicked');

      if (actionType === 'open_lead_form') {
        if (!state.conversation && !state.token) {
          await ensureConversation();
        }
        openLeadForm();
        return;
      }

      if (actionType === 'copy_request') {
        const text = state.lastFailedContent || root.querySelector('textarea[name="chatMessage"]')?.value || '';
        if (text && navigator.clipboard?.writeText) {
          await navigator.clipboard.writeText(text);
          setSummary('Votre demande a été copiée. Vous pouvez la transmettre à OLING par téléphone ou email.', 'info');
        }
        return;
      }

      if (actionType === 'start_diagnostic') {
        await sendStructuredActionMessage(diagnosticPrompt(), CHAT_I18N.loadingDiagnostic || 'Préparation du mini-diagnostic...');
        return;
      }

      if (actionType === 'generate_scoping_note') {
        await sendStructuredActionMessage(scopingNotePrompt(), CHAT_I18N.loadingScopingNote || 'Préparation de la note de cadrage...');
        return;
      }

      if (actionType === 'download_scoping_note') {
        setLoading(true, CHAT_I18N.loadingScopingNotePdf || 'Préparation du PDF...');
        try {
          await downloadScopingNote();
        } catch (error) {
          setError(error.message || (CHAT_I18N.genericError || 'Une erreur est survenue.'));
        } finally {
          setLoading(false);
        }
        return;
      }
    }

    const leadOpenButton = event.target.closest('[data-chat-open-lead]');
    if (leadOpenButton) {
      event.preventDefault();
      if (!state.conversation && !state.token) {
        await ensureConversation();
      }
      openLeadForm();
      return;
    }

    const retryButton = event.target.closest('[data-chat-retry-last]');
    if (!retryButton || state.loading || !state.lastFailedContent) return;

    event.preventDefault();
    setError('');
    setLoading(true, `${CHAT_I18N.sending || 'Envoi en cours'}...`);
    try {
      await ensureConversation();
      await sendMessage(state.lastFailedContent);
      state.lastFailedContent = '';
    } catch (error) {
      renderLocalContinuityMessage(state.lastFailedContent);
      setError('');
    } finally {
      setLoading(false);
    }
  });

  leadButton?.addEventListener('click', async () => {
    if (state.loading) return;
    setError('');
    trackChatEvent('form_submitted');
    trackChatEvent('lead_form_submitted');
    setLoading(true, CHAT_I18N.leadSending || 'Transmission en cours...');
    try {
      await submitLead();
    } catch (error) {
      setError(error.message || (CHAT_I18N.leadSubmitError || 'Impossible de transmettre la demande.'));
    } finally {
      setLoading(false);
    }
  });

  erpForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (state.loading) return;
    setError('');
    setLoading(true, CHAT_I18N.loadingGenerateErp || 'Génération de la synthèse ERP...');
    try {
      await submitErpQuestionnaire();
    } catch (error) {
      setError(error.message || (CHAT_I18N.errorSubmitErp || 'Impossible de transmettre le questionnaire ERP.'));
    } finally {
      setLoading(false);
    }
  });

  resetButton?.addEventListener('click', async () => {
    if (state.loading) return;
    setLoading(true, CHAT_I18N.loadingReset || 'Réinitialisation en cours...');
    try {
      await resetConversation();
    } catch (error) {
      setError(error.message || (CHAT_I18N.resetError || 'Impossible de réinitialiser la conversation.'));
    } finally {
      setLoading(false);
    }
  });

  contactButton?.addEventListener('click', () => {
    setOpen(false);
  });

  root.addEventListener('click', (event) => {
    const link = event.target.closest('a[data-chat-bypass="true"]');
    if (!link) return;
    if (link.classList.contains('oling-chat-widget__source-card')) {
      trackChatEvent('chat_source_clicked');
    }
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

    const href = link.getAttribute('href');
    if (!href) return;

    let url;
    try {
      url = new URL(href, window.location.origin);
    } catch (error) {
      return;
    }

    const isInternalNavigation = (
      (url.protocol === 'http:' || url.protocol === 'https:')
      && url.origin === window.location.origin
      && (!link.target || link.target === '_self')
    );

    if (!isInternalNavigation) return;

    if (mobileBreakpoint.matches) {
      setOpen(false);
    }
  });

  document.addEventListener('click', async (event) => {
    const erpButton = event.target.closest('[data-chat-erp-questionnaire]');
    if (erpButton) {
      event.preventDefault();
      await openErpQuestionnaire();
      return;
    }

    const prefillButton = event.target.closest('[data-chat-prefill]');
    if (prefillButton) {
      event.preventDefault();
      await openAndSendPrefill(prefillButton.dataset.chatPrefill || '');
      return;
    }

    const link = event.target.closest('a');
    if (!link || link.dataset.chatBypass === 'true') return;

    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    if (link.target && link.target !== '_self') return;

    const href = link.getAttribute('href');
    if (!href) return;

    let url;
    try {
      url = new URL(href, window.location.origin);
    } catch (error) {
      return;
    }

    if (url.pathname !== contactPath || url.searchParams.has('chat_fallback') || !link.classList.contains('btn')) {
      return;
    }

    event.preventDefault();
    setOpen(true);
    setError('');
    setLoading(true, CHAT_I18N.openChat || 'Ouverture du chat...');
    try {
      await ensureConversation();
      prefillLeadDescription();
    } catch (error) {
      setError(error.message || (CHAT_I18N.openChatError || 'Impossible d’ouvrir le chat.'));
    } finally {
      setLoading(false);
    }
  });

  restoreConversation().then(async () => {
    state.typing = false;
    syncResetVisibility();
    root.classList.add('is-ready');
    if (window.location.hash === '#chat') {
      setOpen(true);
      if (!state.conversation) {
        await ensureConversation();
      }
      prefillLeadDescription();
      return;
    }

    if (state.open) {
      setOpen(true);
      if (!state.conversation) {
        await ensureConversation();
      }
      prefillLeadDescription();
    }
  });

  updateViewportHeight();
  window.addEventListener('resize', updateViewportHeight);
  window.visualViewport?.addEventListener('resize', updateViewportHeight);
};

document.addEventListener('DOMContentLoaded', initChatWidget);
