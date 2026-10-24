(() => {
  // Delegation also covers KPI HTML refreshed after a Kanban move, and the list view.
  const portfolioMenu = document.querySelector('.portfolio-menu');
  document.addEventListener('click', e => { if (portfolioMenu && !portfolioMenu.contains(e.target)) portfolioMenu.open = false; });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && portfolioMenu?.open) { portfolioMenu.open = false; portfolioMenu.querySelector('summary').focus(); }
  });
  let activeKpi = null;
  function closeKpi(restoreFocus = false) {
    if (!activeKpi) return;
    const trigger = activeKpi.querySelector('.kpi-trigger');
    activeKpi.querySelector('.kpi-popover').hidden = true;
    trigger.setAttribute('aria-expanded', 'false'); activeKpi = null;
    if (restoreFocus) trigger.focus({preventScroll:true});
  }
  function openKpi(kpi) {
    if (activeKpi !== kpi) closeKpi();
    activeKpi = kpi; kpi.querySelector('.kpi-popover').hidden = false;
    kpi.querySelector('.kpi-trigger').setAttribute('aria-expanded', 'true');
    const panel = kpi.querySelector('.kpi-popover'), r = kpi.getBoundingClientRect(), p = panel.getBoundingClientRect();
    panel.style.left = Math.max(12, Math.min(r.left, innerWidth - p.width - 12)) + 'px';
    panel.style.top = Math.max(12, Math.min(kpi.classList.contains('footer-feed') ? r.top - p.height - 4 : r.bottom + 4, innerHeight - p.height - 12)) + 'px';
  }
  document.addEventListener('pointerover', e => {
    const kpi = e.target.closest('.action-kpi');
    if (e.pointerType === 'mouse' && kpi && !kpi.contains(e.relatedTarget)) openKpi(kpi);
  });
  document.addEventListener('pointerout', e => {
    if (activeKpi && activeKpi.contains(e.target) && !activeKpi.contains(e.relatedTarget) && !activeKpi.contains(document.activeElement)) closeKpi();
  });
  document.addEventListener('click', e => {
    if (e.target.closest('.kpi-trigger')) openKpi(e.target.closest('.action-kpi'));
    else if (e.target.closest('.kpi-close')) closeKpi(true);
    else if (activeKpi && !activeKpi.contains(e.target)) closeKpi();
  });
  document.addEventListener('focusout', () => setTimeout(() => {
    if (activeKpi && !activeKpi.contains(document.activeElement) && !activeKpi.matches(':hover')) closeKpi();
  }, 0));
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && activeKpi) closeKpi(true); });
  window.addEventListener('resize', () => closeKpi());
  const board = document.querySelector('.board'); if (!board) return;
  const scroller = document.querySelector('.board-scroll');
  function fitBoard() {
    scroller.firstElementChild.style.width = board.scrollWidth + 'px';
    const overflow = board.scrollWidth > board.clientWidth + 2;
    scroller.hidden = !overflow;
    document.querySelector('.column-jump').classList.toggle('needed', overflow);
    const footerHeight = document.querySelector('.footer').getBoundingClientRect().height;
    board.style.height = Math.max(260, innerHeight - (board.getBoundingClientRect().top + scrollY) - footerHeight - 8) + 'px';
  }
  scroller.addEventListener('scroll', () => { if (Math.abs(board.scrollLeft - scroller.scrollLeft) > 1) board.scrollLeft = scroller.scrollLeft; });
  board.addEventListener('scroll', () => { if (Math.abs(board.scrollLeft - scroller.scrollLeft) > 1) scroller.scrollLeft = board.scrollLeft; });
  document.querySelector('.column-jump').addEventListener('click', e => {
    const button = e.target.closest('[data-column]'); if (!button) return;
    const lane = [...board.children].find(x => x.dataset.value === button.dataset.column);
    board.scrollLeft = lane.offsetLeft - board.offsetLeft; lane.focus({preventScroll:true});
  });
  window.addEventListener('resize', fitBoard);
  requestAnimationFrame(fitBoard);
  const chromeObserver = new ResizeObserver(() => requestAnimationFrame(fitBoard));
  for (const el of document.querySelectorAll('.app-header,.workspace-toolbar,.footer')) chromeObserver.observe(el);
  const error = document.getElementById('save-error'), feedback = document.getElementById('save-status');
  const field = board.dataset.field || 'status';
  const closingTargets = [...document.querySelectorAll('.outcome-drop')];
  let saving = false, permissionDenied = false;
  let dragged = null, pointer = null, frame = null, feedbackTimer;
  let previewCard = null;
  function closePreview() {
    if (!previewCard) return;
    previewCard.querySelector('.card-details').hidden = true;
    previewCard.querySelector('.card-details-toggle').setAttribute('aria-expanded', 'false');
    previewCard.classList.remove('preview-open'); previewCard = null;
  }
  function openPreview(card) {
    if (dragged || card.dataset.saving) return;
    if (previewCard !== card) closePreview();
    previewCard = card; const panel = card.querySelector('.card-details');
    panel.hidden = false; card.classList.add('preview-open');
    card.querySelector('.card-details-toggle').setAttribute('aria-expanded', 'true');
    const r = card.getBoundingClientRect(), p = panel.getBoundingClientRect();
    const right = r.right + 8, left = r.left - p.width - 8;
    panel.style.left = Math.max(12, Math.min(right + p.width <= innerWidth - 12 ? right : left >= 12 ? left : r.left, innerWidth - p.width - 12)) + 'px';
    panel.style.top = Math.max(12, Math.min(r.top, innerHeight - p.height - 12)) + 'px';
  }
  board.addEventListener('click', e => {
    const card = e.target.closest('.deal-card'); if (!card) return;
    if (e.target.closest('.card-details-toggle')) {
      if (previewCard === card) closePreview(); else openPreview(card);
    } else if (e.target.closest('.card-details-close')) {
      closePreview(); card.querySelector('.card-details-toggle').focus({preventScroll:true});
    }
  });
  board.addEventListener('focusout', () => setTimeout(() => {
    if (previewCard && !previewCard.contains(document.activeElement)) closePreview();
  }, 0));
  document.addEventListener('pointerdown', e => { if (previewCard && !previewCard.contains(e.target)) closePreview(); });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && previewCard) { const button = previewCard.querySelector('.card-details-toggle'); closePreview(); button.focus(); }
  });
  window.addEventListener('resize', closePreview);
  function announce(message) {
    clearTimeout(feedbackTimer); feedback.textContent = message; feedback.hidden = false;
    feedbackTimer = setTimeout(() => { feedback.hidden = true; }, 5000);
  }
  function clearDrag() {
    dragged?.classList.remove('dragging'); dragged = null; pointer = null;
    cancelAnimationFrame(frame); frame = null;
    document.querySelectorAll('.drop-target').forEach(x => x.classList.remove('drop-target'));
  }
  function scrollWhileDragging() {
    if (!dragged || !pointer) { frame = null; return; }
    const r = board.getBoundingClientRect();
    if (pointer.y >= r.top && pointer.y <= r.bottom) {
      const right = Math.min(r.right, innerWidth), left = Math.max(r.left, 0);
      board.scrollLeft += pointer.x > right - 65 ? 12 : pointer.x < left + 65 ? -12 : 0;
    }
    frame = requestAnimationFrame(scrollWhileDragging);
  }
  async function save(card, value) {
    const select = card.querySelector('.card-status'), before = card.closest('.lane').dataset.value;
    if (saving || permissionDenied || card.dataset.saving || value === before) {
      if (select) select.value = before;
      return;
    }
    saving = true;
    closingTargets.forEach(target => {
      target.setAttribute('aria-disabled', 'true');
      if (target.dataset.dropStatus === value) target.setAttribute('aria-busy', 'true');
    });
    const keyboardMove = document.activeElement === select;
    card.dataset.saving = '1'; card.setAttribute('aria-busy', 'true'); card.draggable = false;
    error.hidden = true; announce('Enregistrement…'); if (select) select.disabled = true;
    try {
      const url = new URL(card.dataset.url, location.href);
      url.search = location.search; // Same filter context for authoritative closing totals.
      const response = await fetch(url, {method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/x-www-form-urlencoded','Accept':'application/json'}, body:new URLSearchParams({_token:board.dataset.token,version:card.dataset.version,[field]:value})});
      const data = await response.json().catch(() => ({}));
      if (response.redirected || response.status === 401 || response.status === 403) {
        permissionDenied = true;
        board.querySelectorAll('.deal-card').forEach(item => { item.draggable = false; });
        board.querySelectorAll('.card-status').forEach(item => { item.disabled = true; });
        throw new Error('Session expirée ou permission insuffisante. Rechargez la page pour vérifier vos accès.');
      }
      if (!response.ok || !data.saved) throw new Error(data.error || 'Enregistrement impossible. Vérifiez votre connexion et rechargez la page.');
      card.dataset.version = data.version;
      for (const [status, count] of Object.entries(data.closed_counts || {})) {
        const counter = document.querySelector(`[data-closed-count="${status}"]`);
        if (counter) counter.textContent = count;
      }
      closePreview(); closeKpi();
      if (data.deadline_html) document.getElementById('deadline-summary').innerHTML = data.deadline_html;
      const lane = [...board.querySelectorAll('.lane')].find(x => x.dataset.value === value);
      const hiddenByFilter = (board.dataset.due && data.due_matches?.[board.dataset.due] === false)
        || (board.dataset.statusFilter && board.dataset.statusFilter !== value)
        || (board.dataset.view === 'todo' && value === 'qualify' && card.dataset.selectedWatch !== '1');
      if (lane && !hiddenByFilter) {
        lane.querySelector('.lane-cards').append(card);
        if (data.card_html) {
          const template = document.createElement('template'); template.innerHTML = data.card_html.trim();
          const updated = template.content.firstElementChild; card.replaceWith(updated);
          if (keyboardMove) updated.querySelector('.card-details-toggle').focus({preventScroll:true});
        } else if (select) select.value = value;
        announce('Déplacement enregistré');
      } else {
        card.remove(); board.dataset.total = Math.max(0, Number(board.dataset.total) - 1);
        document.querySelectorAll('[data-board-total]').forEach(el => { el.textContent = board.dataset.total; });
        if (keyboardMove) (document.querySelector('select[name="scope"]') || board).focus({preventScroll:true});
        announce('Statut enregistré. La fiche reste accessible dans le portefeuille avec le filtre Toutes les étapes.');
      }
      for (const col of board.querySelectorAll('.lane')) col.querySelector('header > span:last-child').textContent = col.querySelectorAll('.deal-card').length;
      fitBoard();
    } catch (e) {
      if (select) select.value = before; feedback.hidden = true;
      error.textContent = e.message; error.hidden = false; error.scrollIntoView({block:'nearest'});
    } finally {
      saving = false;
      closingTargets.forEach(target => {
        target.removeAttribute('aria-busy');
        if (!permissionDenied) target.removeAttribute('aria-disabled');
      });
      delete card.dataset.saving; card.removeAttribute('aria-busy'); card.draggable = !permissionDenied;
      if (select) select.disabled = permissionDenied;
    }
  }
  board.addEventListener('change', e => { if (e.target.matches('.card-status')) save(e.target.closest('.deal-card'), e.target.value); });
  board.addEventListener('dragstart', e => {
    const card = e.target.closest('.deal-card');
    if (!card || saving || permissionDenied || card.dataset.saving || e.target.closest('select,input,button')) { e.preventDefault(); return; }
    closePreview(); dragged = card; card.classList.add('dragging');
    e.dataTransfer.setData('text/plain', card.dataset.id); e.dataTransfer.effectAllowed = 'move';
  });
  document.addEventListener('dragover', e => {
    if (!dragged) return;
    e.preventDefault(); e.dataTransfer.dropEffect = 'move'; pointer = {x:e.clientX,y:e.clientY};
    const lane = e.target.closest('.lane,.outcome-drop');
    document.querySelectorAll('.lane,.outcome-drop').forEach(x => x.classList.toggle('drop-target', x === lane));
    if (!frame) frame = requestAnimationFrame(scrollWhileDragging);
  });
  document.addEventListener('drop', e => {
    if (!dragged) return; e.preventDefault();
    const card = dragged, lane = e.target.closest('.lane,.outcome-drop'); clearDrag();
    if (lane) save(card, lane.dataset.dropStatus || lane.dataset.value);
  });
  closingTargets.forEach(target => {
    target.addEventListener('click', e => { if (saving || permissionDenied) e.preventDefault(); });

  });
  board.addEventListener('dragleave', e => {
    if (!board.contains(e.relatedTarget)) {
      document.querySelectorAll('.drop-target').forEach(x => x.classList.remove('drop-target'));
      pointer = null;
    }
  });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') clearDrag(); });
  document.addEventListener('dragend', clearDrag);
})();
