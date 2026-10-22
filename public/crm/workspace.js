(() => {
  const board = document.querySelector('.board'); if (!board) return;
  const error = document.getElementById('save-error'); let dragged = null;
  async function save(card, status) {
    if (card.dataset.saving) return;
    card.dataset.saving = '1'; card.setAttribute('aria-busy', 'true'); error.hidden = true;
    const select = card.querySelector('.card-status'); const before = card.closest('[data-status]')?.dataset.status;
    if(select) select.disabled = true;
    try {
      const response = await fetch(card.dataset.url, {method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/x-www-form-urlencoded','Accept':'application/json'}, body:new URLSearchParams({_token:board.dataset.token,version:card.dataset.version,status})});
      const data = await response.json().catch(() => ({}));
      if (!response.ok || !data.saved) throw new Error(data.error || 'Enregistrement impossible. Vérifiez votre connexion et rechargez la page.');
      card.dataset.version = data.version;
      const lane = [...board.querySelectorAll('[data-status]')].find(x=>x.dataset.status===status);
      lane?.querySelector('.lane-cards').append(card); if(select) select.value=status;
      for(const col of board.querySelectorAll('.lane')) col.querySelector('header > span:last-child').textContent=col.querySelectorAll('.deal-card').length;
    } catch(e) { if(select && before) select.value=before; error.textContent=e.message; error.hidden=false; error.scrollIntoView({block:'nearest'}); }
    finally { delete card.dataset.saving; card.removeAttribute('aria-busy'); if(select) select.disabled=false; }
  }
  board.addEventListener('change',e=>{if(e.target.matches('.card-status')) save(e.target.closest('.deal-card'),e.target.value);});
  board.addEventListener('dragstart',e=>{const card=e.target.closest('.deal-card[draggable="true"]');if(!card)return;dragged=card;e.dataTransfer.setData('text/plain',card.dataset.id);e.dataTransfer.effectAllowed='move';});
  board.addEventListener('dragend',()=>{dragged=null;board.querySelectorAll('.drop-target').forEach(x=>x.classList.remove('drop-target'));});
  for(const lane of board.querySelectorAll('[data-status]')) {
    lane.addEventListener('dragover',e=>{if(dragged){e.preventDefault();lane.classList.add('drop-target');}});
    lane.addEventListener('dragleave',e=>{if(!lane.contains(e.relatedTarget))lane.classList.remove('drop-target');});
    lane.addEventListener('drop',e=>{e.preventDefault();lane.classList.remove('drop-target');if(dragged)save(dragged,lane.dataset.status);});
  }
})();
