const CHAT_STORAGE_KEY = 'oling_chat_conversation_token';
const CHAT_OPEN_STATE_KEY = 'oling_chat_open_state';

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

const sourceLabel = (url) => {
  try {
    const parsed = new URL(url, window.location.origin);
    return parsed.pathname;
  } catch (error) {
    return url;
  }
};

const formatMessageContent = (value) => escapeHtml(value).replace(/\n/g, '<br>');

const sourceTypeLabel = (type) => {
  const labels = {
    page: 'Page',
    expertise: 'Expertise',
    service: 'Service',
    cas_client: 'Cas client',
    equipe: 'Équipe',
  };

  return labels[type] || 'Ressource';
};

const getMessageSourceCards = (message) => (
  message.sourceCards && message.sourceCards.length
    ? message.sourceCards
    : (message.sources || []).map((url) => ({
        url,
        title: sourceLabel(url),
        type: 'page',
        typeLabel: 'Ressource',
        image: null,
        excerpt: '',
      }))
);

const createSourceCardsHtml = (cards) => `
  <div class="oling-chat-widget__sources-inline">
    ${cards.map((card) => `
      <a class="oling-chat-widget__source-card oling-chat-widget__source-card--inline" href="${escapeHtml(card.url)}" target="_blank" rel="noopener" data-chat-bypass="true">
        ${card.image ? `<span class="oling-chat-widget__source-media"><img src="${escapeHtml(card.image)}" alt="${escapeHtml(card.title)}" loading="lazy"></span>` : '<span class="oling-chat-widget__source-media oling-chat-widget__source-media--placeholder"></span>'}
        <span class="oling-chat-widget__source-body">
          <span class="oling-chat-widget__source-type">${escapeHtml(card.typeLabel || sourceTypeLabel(card.type))}</span>
          <span class="oling-chat-widget__source-title">${escapeHtml(card.title || sourceLabel(card.url))}</span>
          ${card.excerpt ? `<span class="oling-chat-widget__source-excerpt">${escapeHtml(card.excerpt)}</span>` : ''}
        </span>
      </a>
    `).join('')}
  </div>
`;

const createMessageHtml = (message) => `
  <article class="oling-chat-widget__message oling-chat-widget__message--${message.role}">
    <div class="oling-chat-widget__message-meta">${message.role === 'assistant' ? 'OLING' : 'Vous'}</div>
    <div class="oling-chat-widget__bubble">${formatMessageContent(message.content)}</div>
    ${message.role === 'assistant' && getMessageSourceCards(message).length ? createSourceCardsHtml(getMessageSourceCards(message)) : ''}
  </article>
`;

const createTypingHtml = () => `
  <article class="oling-chat-widget__message oling-chat-widget__message--assistant oling-chat-widget__message--typing">
    <div class="oling-chat-widget__message-meta">OLING</div>
    <div class="oling-chat-widget__bubble">
      <span class="oling-chat-widget__typing" aria-hidden="true">
        <span></span><span></span><span></span>
      </span>
    </div>
  </article>
`;

const initChatWidget = () => {
  const root = document.getElementById('oling-chat-widget');
  if (!root) return;

  const launcher = root.querySelector('.oling-chat-widget__launcher');
  const panel = root.querySelector('.oling-chat-widget__panel');
  const closeButton = root.querySelector('.oling-chat-widget__close');
  const messages = root.querySelector('[data-chat-messages]');
  const leadBlock = root.querySelector('[data-chat-lead]');
  const errorBox = root.querySelector('[data-chat-error]');
  const statusBox = root.querySelector('[data-chat-status]');
  const summaryBox = root.querySelector('[data-chat-summary]');
  const composer = root.querySelector('[data-chat-composer]');
  const messageInput = composer?.querySelector('textarea[name="chatMessage"]');
  const leadButton = root.querySelector('[data-chat-submit-lead]');
  const submitButton = composer?.querySelector('button[type="submit"]');
  const resetButton = root.querySelector('[data-chat-reset]');
  const contactButton = root.querySelector('.oling-chat-widget__composer-tools a[data-chat-bypass="true"]');
  const contactPath = new URL(root.dataset.contactFallbackUrl, window.location.origin).pathname;
  const defaultPlaceholder = messageInput?.getAttribute('placeholder') || '';
  const defaultLeadLabel = leadButton?.textContent || 'Transmettre la demande';

  const state = {
    token: window.localStorage.getItem(CHAT_STORAGE_KEY),
    open: window.localStorage.getItem(CHAT_OPEN_STATE_KEY) === 'open',
    loading: false,
    conversation: null,
    typing: false,
  };

  const setOpen = (open) => {
    state.open = open;
    window.localStorage.setItem(CHAT_OPEN_STATE_KEY, open ? 'open' : 'closed');
    root.classList.toggle('is-open', open);
    root.classList.toggle('is-closed', !open);
    document.body.classList.toggle('has-open-chat-widget', open);
    launcher?.setAttribute('aria-expanded', open ? 'true' : 'false');
    panel?.setAttribute('aria-hidden', open ? 'false' : 'true');
    if (open) {
      messageInput?.focus();
    }
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
    closeButton?.toggleAttribute('disabled', loading);
    launcher?.toggleAttribute('disabled', loading && !state.open);
    submitButton?.setAttribute('aria-label', loading ? 'Envoi en cours' : 'Envoyer');
    submitButton?.setAttribute('title', loading ? 'Envoi en cours' : 'Envoyer');
    if (leadButton) {
      leadButton.textContent = loading ? 'En cours...' : defaultLeadLabel;
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

  const scrollMessagesToBottom = () => {
    if (!messages) return;
    messages.scrollTop = messages.scrollHeight;
  };

  const renderMessageList = (messageList = []) => {
    if (!messages) return;

    messages.innerHTML = messageList.length
      ? messageList.map(createMessageHtml).join('') + (state.loading && state.typing ? createTypingHtml() : '')
      : '<div class="oling-chat-widget__empty">La conversation commence ici.</div>';
    scrollMessagesToBottom();
  };

  const syncResetVisibility = (conversation = state.conversation) => {
    if (!resetButton) return;
    const hasVisitorMessage = (conversation?.messages || []).some((message) => message.role === 'visitor');
    resetButton.classList.toggle('d-none', !hasVisitorMessage);
  };

  const renderConversation = (conversation) => {
    state.conversation = conversation;
    if (!messages) return;

    const messageList = conversation.messages || [];
    const lastMessage = messageList.length ? messageList[messageList.length - 1] : null;
    if (!lastMessage || lastMessage.role === 'assistant') {
      state.typing = false;
    }
    syncResetVisibility(conversation);
    renderMessageList(messageList);
    setLeadVisible(!!conversation.requestLead && !conversation.leadSubmitted);

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
        conversation.summaryShort || 'Demande bien envoyée. Vous pouvez continuer la conversation, ajouter une précision ou poser une autre question.',
        'success'
      );
      if (messageInput) {
        messageInput.placeholder = 'Ajouter un complément, une précision ou un autre besoin...';
      }
      return;
    }

    setSummary(conversation.requestLead ? 'Si vous souhaitez être recontacté, vous pouvez laisser vos coordonnées ci-dessous. Vous pouvez aussi continuer à préciser votre besoin.' : '', 'info');
    if (messageInput) {
      messageInput.placeholder = defaultPlaceholder;
    }
    if (submitButton) {
      submitButton.setAttribute('aria-label', 'Envoyer');
      submitButton.setAttribute('title', 'Envoyer');
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
      throw new Error(payload.message || 'Une erreur est survenue.');
    }

    return payload;
  };

  const showUrl = (token) => root.dataset.showUrlTemplate.replace('CHAT_TOKEN', token);
  const messageUrl = (token) => root.dataset.messageUrlTemplate.replace('CHAT_TOKEN', token);
  const leadUrl = (token) => root.dataset.leadUrlTemplate.replace('CHAT_TOKEN', token);

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

  const prefillLeadDescription = () => {
    const field = root.querySelector('textarea[name="chatNeedDescription"]');
    if (!field || field.value.trim() !== '' || !state.conversation) return;

    const visitorMessages = (state.conversation.messages || [])
      .filter((message) => message.role === 'visitor')
      .map((message) => message.content);

    field.value = visitorMessages.join('\n').trim();
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

    state.typing = false;
    renderConversation(payload.conversation);
    setStatus('');
    prefillLeadDescription();
  };

  const renderOptimisticVisitorMessage = (content) => {
    const optimisticConversation = {
      ...(state.conversation || {}),
      messages: [...(state.conversation?.messages || []), { role: 'visitor', content }],
    };

    state.typing = true;
    renderConversation(optimisticConversation);
    setStatus('OLING rédige sa réponse...');
  };

  const resetConversation = async () => {
    window.localStorage.removeItem(CHAT_STORAGE_KEY);
    state.token = null;
    state.conversation = null;
    setError('');
    setSummary('');
    setStatus('');
    setLeadVisible(false);
    syncResetVisibility(null);
    if (messages) {
      messages.innerHTML = '<div class="oling-chat-widget__empty">La conversation commence ici.</div>';
    }
    if (messageInput) {
      messageInput.value = '';
      messageInput.placeholder = defaultPlaceholder;
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

    renderConversation(response.conversation);
    setError('');
  };

  launcher?.addEventListener('click', async () => {
    setOpen(true);
    setError('');
    state.typing = false;
    setLoading(true, 'Ouverture du chat...');
    try {
      await ensureConversation();
      prefillLeadDescription();
    } catch (error) {
      setError(error.message || 'Impossible d’ouvrir le chat.');
    } finally {
      setLoading(false);
    }
  });

  closeButton?.addEventListener('click', () => setOpen(false));

  composer?.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (state.loading) return;

    const content = messageInput?.value.trim() || '';
    if (!content) return;

    setError('');
    messageInput.value = '';
    setLoading(true, 'Envoi en cours...');
    const previousConversation = state.conversation ? { ...state.conversation, messages: [...(state.conversation.messages || [])] } : null;
    try {
      await ensureConversation();
      renderOptimisticVisitorMessage(content);
      await sendMessage(content);
      state.typing = false;
      setStatus('');
    } catch (error) {
      state.typing = false;
      if (previousConversation) {
        renderConversation(previousConversation);
      }
      setError(error.message || 'Impossible d’envoyer le message.');
    } finally {
      setLoading(false);
    }
  });

  leadButton?.addEventListener('click', async () => {
    if (state.loading) return;
    setError('');
    setLoading(true, 'Transmission en cours...');
    try {
      await submitLead();
    } catch (error) {
      setError(error.message || 'Impossible de transmettre la demande.');
    } finally {
      setLoading(false);
    }
  });

  resetButton?.addEventListener('click', async () => {
    if (state.loading) return;
    setLoading(true, 'Réinitialisation en cours...');
    try {
      await resetConversation();
    } catch (error) {
      setError(error.message || 'Impossible de réinitialiser la conversation.');
    } finally {
      setLoading(false);
    }
  });

  contactButton?.addEventListener('click', () => {
    setOpen(false);
  });

  document.addEventListener('click', async (event) => {
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
    setLoading(true, 'Ouverture du chat...');
    try {
      await ensureConversation();
      prefillLeadDescription();
    } catch (error) {
      setError(error.message || 'Impossible d’ouvrir le chat.');
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
};

document.addEventListener('DOMContentLoaded', initChatWidget);
