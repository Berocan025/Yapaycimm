// diziportal - DG SPORTS frontend | Developer: DiziPortal.Com
(function(){
  const api = {
    publicData: '/api/public_data.php',
    viewerPing: '/api/viewer_ping.php'
  };

  const el = {
    matches: document.getElementById('matches_list'),
    channels: document.getElementById('channels_list'),
    heroTitle: document.getElementById('hero_title'),
    heroSubtitle: document.getElementById('hero_subtitle'),
    tg: document.getElementById('tg_link'),
    ig: document.getElementById('ig_link'),
    tw: document.getElementById('tw_link'),
    tt: document.getElementById('tt_link'),
    ctg: document.getElementById('c_tg'),
    cig: document.getElementById('c_ig'),
    ctw: document.getElementById('c_tw'),
    ctt: document.getElementById('c_tt'),
    year: document.getElementById('brand_year'),
    modal: document.getElementById('player_modal'),
    modalClose: document.getElementById('modal_close'),
    playerWrap: document.getElementById('player_container'),
    metaLogo: document.getElementById('meta_logo'),
    metaTitle: document.getElementById('meta_title'),
    metaSub: document.getElementById('meta_sub'),
    liveViewers: document.getElementById('live_viewers'),
  };

  let currentPlayer = null;
  let pingTimer = null;
  let currentItem = null; // {type, id}
  let playerLogoUrl = '';

  function setSocialLinks(settings){
    const map = {
      telegram_url: [el.tg, el.ctg],
      instagram_url: [el.ig, el.cig],
      twitter_url: [el.tw, el.ctw],
      tiktok_url: [el.tt, el.ctt]
    };
    for(const key in map){
      const url = settings[key] || '#';
      map[key].forEach(a => { a.href = url || '#'; a.style.display = url ? '' : 'none'; });
    }
  }

  function formatTime(ts){
    try{
      const d = new Date(ts + 'Z');
      return d.toLocaleString();
    }catch(e){ return ts }
  }

  function viewerLabel(n){
    return `${n} izleyici`;
  }

  function cardMatch(m){
    const div = document.createElement('div');
    div.className = 'card';
    div.innerHTML = `
      <div class="thumb">
        <img class="logo" src="${m.team_a_logo || '/assets/icons/ball.svg'}" alt="${m.team_a_name}">
      </div>
      <div class="content">
        <h3 class="title">${m.team_a_name} <span class="badge">VS</span> ${m.team_b_name}</h3>
        <div class="meta">
          <span>⏱ ${formatTime(m.match_time)}</span>
          ${m.stadium ? `<span>🏟 ${m.stadium}</span>`: ''}
          <span class="badge" data-viewers="${m.id}" data-type="match">${viewerLabel(m.viewers || 0)}</span>
        </div>
      </div>
      ${m.stream_url ? `<button class="play-btn" data-type="match" data-id="${m.id}" data-title="${m.team_a_name} VS ${m.team_b_name}" data-logo="${m.team_a_logo}" data-sub="${m.stadium || ''}" data-url="${m.stream_url}">▶ İzle</button>`: ''}
    `;
    return div;
  }

  function cardChannel(c){
    const div = document.createElement('div');
    div.className = 'card';
    div.innerHTML = `
      <div class="thumb">
        <img class="logo" src="${c.logo_url || '/assets/icons/tv.svg'}" alt="${c.name}">
      </div>
      <div class="content">
        <h3 class="title">${c.name}</h3>
        <div class="meta">
          <span class="badge" data-viewers="${c.id}" data-type="channel">${viewerLabel(c.viewers || 0)}</span>
        </div>
      </div>
      <button class="play-btn" data-type="channel" data-id="${c.id}" data-title="${c.name}" data-logo="${c.logo_url}" data-sub="7/24 Yayın" data-url="${c.stream_url}">▶ İzle</button>
    `;
    return div;
  }

  function render(data){
    el.year.textContent = new Date().getFullYear();
    el.heroTitle.textContent = data.settings.hero_title || 'Canlı Maçlar';
    el.heroSubtitle.textContent = data.settings.hero_subtitle || '';
    setSocialLinks(data.settings);
    playerLogoUrl = data.settings.player_logo_url || '';

    el.matches.innerHTML = '';
    (data.matches || []).forEach(m => el.matches.appendChild(cardMatch(m)));
    el.channels.innerHTML = '';
    (data.channels || []).forEach(c => el.channels.appendChild(cardChannel(c)));

    document.querySelectorAll('.play-btn').forEach(btn => btn.addEventListener('click', onPlayClick));
  }

  function onPlayClick(e){
    const btn = e.currentTarget;
    const itemType = btn.getAttribute('data-type');
    const id = parseInt(btn.getAttribute('data-id'));
    const title = btn.getAttribute('data-title');
    const logo = btn.getAttribute('data-logo') || '';
    const sub = btn.getAttribute('data-sub') || '';
    const url = btn.getAttribute('data-url');
    openPlayer({ itemType, id, title, logo, sub, url });
  }

  function destroyPlayer(){
    if(currentPlayer && typeof currentPlayer.api === 'function'){
      try{ currentPlayer.api('destroy'); }catch(e){}
    }
    currentPlayer = null;
    el.playerWrap.innerHTML = '';
  }

  function openPlayer({itemType, id, title, logo, sub, url}){
    destroyPlayer();
    el.modal.classList.remove('hidden');
    el.metaLogo.src = logo || '/assets/icons/tv.svg';
    el.metaTitle.textContent = title;
    el.metaSub.textContent = sub || '';
    el.liveViewers.textContent = viewerLabel(0);

    // PlayerJS integration
    const pid = 'player_' + Date.now();
    const container = document.createElement('div');
    container.id = pid;
    el.playerWrap.appendChild(container);

    const cfg = {
      id: pid,
      file: url,
      poster: logo || '',
      autoplay: 1,
      hls: 1,
      skin: 's1',
      title: title,
      logo: playerLogoUrl || '', // top-right logo from admin
      loop: 0,
      live: 1
    };
    try{
      currentPlayer = new Playerjs(cfg);
    }catch(err){
      console.error('PlayerJS error', err);
    }

    currentItem = { type: itemType, id };
    startPing();
  }

  function closeModal(){
    stopPing();
    destroyPlayer();
    el.modal.classList.add('hidden');
  }

  function startPing(){
    stopPing();
    const doPing = () => {
      if(!currentItem) return;
      const body = new URLSearchParams({
        item_type: currentItem.type,
        item_id: String(currentItem.id)
      });
      fetch(api.viewerPing, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body })
        .then(r => r.json()).then(d => {
          if(d && typeof d.viewers === 'number'){
            el.liveViewers.textContent = viewerLabel(d.viewers);
            const badges = document.querySelectorAll(`[data-viewers="${currentItem.id}"][data-type="${currentItem.type}"]`);
            badges.forEach(b => b.textContent = viewerLabel(d.viewers));
          }
        }).catch(()=>{});
    };
    doPing();
    pingTimer = setInterval(doPing, 15000);
  }

  function stopPing(){
    if(pingTimer) clearInterval(pingTimer);
    pingTimer = null;
    currentItem = null;
  }

  el.modalClose.addEventListener('click', closeModal);
  el.modal.addEventListener('click', (e)=>{ if(e.target === el.modal) closeModal(); });

  fetch(api.publicData).then(r => r.json()).then(render).catch(err => {
    console.error(err);
  });
})();