(() => {
  const board = document.querySelector('.board'); if (!board) return;
  const error = document.getElementById('save-error'), feedback = document.getElementById('save-status');
  const field = board.dataset.field || 'status';
  let dragged = null, pointer = null, frame = null, feedbackTimer;
  function announce(message) {
    clearTimeout(feedbackTimer); feedback.textContent = message; feedback.hidden = false;
    feedbackTimer = setTimeout(() => { feedback.hidden = true; }, 5000);
  }
  function clearDrag() {
    dragged?.classList.remove('dragging'); dragged = null; pointer = null;
    cancelAnimationFrame(frame); frame = null;
    board.querySelectorAll('.drop-target').forEach(x => x.classList.remove('drop-target'));
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
    if (card.dataset.saving || value === before) return;
    card.dataset.saving = '1'; card.setAttribute('aria-busy', 'true'); card.draggable = false;
    error.hidden = true; announce('Enregistrement…'); if (select) select.disabled = true;
    try {
      const response = await fetch(card.dataset.url, {method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/x-www-form-urlencoded','Accept':'application/json'}, body:new URLSearchParams({_token:board.dataset.token,version:card.dataset.version,[field]:value})});
      const data = await response.json().catch(() => ({}));
      if (!response.ok || !data.saved) throw new Error(data.error || 'Enregistrement impossible. Vérifiez votre connexion et rechargez la page.');
      card.dataset.version = data.version;
      const lane = [...board.querySelectorAll('.lane')].find(x => x.dataset.value === value);
      lane.querySelector('.lane-cards').append(card); if (select) select.value = value;
      if (field === 'decision') card.querySelector('.crm-badge').textContent = select.selectedOptions[0].textContent;
      for (const col of board.querySelectorAll('.lane')) col.querySelector('header > span:last-child').textContent = col.querySelectorAll('.deal-card').length;
      announce('Déplacement enregistré');
    } catch (e) {
      if (select) select.value = before; feedback.hidden = true;
      error.textContent = e.message; error.hidden = false; error.scrollIntoView({block:'nearest'});
    } finally {
      delete card.dataset.saving; card.removeAttribute('aria-busy'); card.draggable = true;
      if (select) select.disabled = false;
    }
  }
  board.addEventListener('change', e => { if (e.target.matches('.card-status')) save(e.target.closest('.deal-card'), e.target.value); });
  board.addEventListener('dragstart', e => {
    const card = e.target.closest('.deal-card');
    if (!card || card.dataset.saving || e.target.closest('select,input,button')) { e.preventDefault(); return; }
    dragged = card; card.classList.add('dragging');
    e.dataTransfer.setData('text/plain', card.dataset.id); e.dataTransfer.effectAllowed = 'move';
  });
  board.addEventListener('dragover', e => {
    if (!dragged) return;
    e.preventDefault(); e.dataTransfer.dropEffect = 'move'; pointer = {x:e.clientX,y:e.clientY};
    const lane = e.target.closest('.lane');
    board.querySelectorAll('.lane').forEach(x => x.classList.toggle('drop-target', x === lane));
    if (!frame) frame = requestAnimationFrame(scrollWhileDragging);
  });
  board.addEventListener('drop', e => {
    if (!dragged) return; e.preventDefault();
    const card = dragged, lane = e.target.closest('.lane'); clearDrag();
    if (lane) save(card, lane.dataset.value);
  });
  document.addEventListener('dragend', clearDrag);
})();
