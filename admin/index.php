<?php
// diziportal - Admin Panel
// Developer: DiziPortal.Com
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/init_db.php';
start_session_if_needed();
$csrf = csrf_token();
?><!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>DG SPORTS | Admin - DiziPortal.Com</title>
  <link rel="stylesheet" href="/assets/styles.css?v=<?php echo time(); ?>">
  <style>
    .admin{max-width:1100px;margin:20px auto;padding:0 16px}
    .card{padding:12px}
    .row{display:flex;gap:12px;flex-wrap:wrap}
    .col{flex:1 1 280px}
    label{display:block;font-size:12px;color:#aaa;margin-bottom:6px}
    input,select,textarea{width:100%;padding:10px;border-radius:10px;background:#0f0f14;border:1px solid rgba(255,255,255,.08);color:#eee}
    table{width:100%;border-collapse:collapse}
    th,td{padding:10px;border-bottom:1px solid rgba(255,255,255,.06);font-size:14px}
    .actions{display:flex;gap:8px}
    .small{font-size:12px;color:#aaa}
    .btn{cursor:pointer}
    .right{float:right}
  </style>
</head>
<body>
  <div class="admin">
    <h1>DG SPORTS Admin <span class="small">Geliştirici: DiziPortal.Com</span></h1>

    <div id="login_card" class="card" style="display:none">
      <h3>Giriş</h3>
      <div class="row">
        <div class="col">
          <label>Kullanıcı Adı</label>
          <input id="l_user" type="text" placeholder="admin">
        </div>
        <div class="col">
          <label>Şifre</label>
          <input id="l_pass" type="password" placeholder="admin123">
        </div>
      </div>
      <button class="btn primary" id="btn_login">Giriş Yap</button>
    </div>

    <div id="dashboard" style="display:none">
      <div class="card">
        <div class="row">
          <div class="col">
            <h3>Ayarlar</h3>
            <label>Marka Adı</label>
            <input id="s_brand" type="text">
            <label>Player Logo URL (sağ üst)</label>
            <input id="s_logo" type="text">
            <label>Hero Başlık</label>
            <input id="s_h1" type="text">
            <label>Hero Alt Başlık</label>
            <input id="s_h2" type="text">
            <div class="row">
              <div class="col">
                <label>Telegram URL</label>
                <input id="s_tg" type="text">
              </div>
              <div class="col">
                <label>Instagram URL</label>
                <input id="s_ig" type="text">
              </div>
              <div class="col">
                <label>Twitter URL</label>
                <input id="s_tw" type="text">
              </div>
              <div class="col">
                <label>TikTok URL</label>
                <input id="s_tt" type="text">
              </div>
            </div>
            <button class="btn primary" id="btn_save_settings">Kaydet</button>
            <span class="small" id="s_msg"></span>
          </div>
          <div class="col">
            <h3>Logo Yükle</h3>
            <input id="up_file" type="file" accept="image/*">
            <button class="btn ghost" id="btn_upload">Yükle</button>
            <div class="small" id="up_msg"></div>
          </div>
        </div>
      </div>

      <div class="card">
        <h3>Kanallar</h3>
        <div class="row">
          <div class="col">
            <label>İsim</label>
            <input id="c_name" type="text">
          </div>
          <div class="col">
            <label>Logo URL</label>
            <input id="c_logo" type="text">
          </div>
          <div class="col">
            <label>Yayın URL</label>
            <input id="c_stream" type="text">
          </div>
          <div class="col">
            <label>Aktif</label>
            <select id="c_active"><option value="1">Evet</option><option value="0">Hayır</option></select>
          </div>
        </div>
        <button class="btn primary" id="btn_add_channel">Ekle</button>
        <table>
          <thead><tr><th>ID</th><th>İsim</th><th>Logo</th><th>Stream</th><th>Aktif</th><th>İşlem</th></tr></thead>
          <tbody id="tbl_channels"></tbody>
        </table>
      </div>

      <div class="card">
        <h3>Maçlar</h3>
        <div class="row">
          <div class="col"><label>Takım A</label><input id="m_a" type="text"></div>
          <div class="col"><label>A Logo URL</label><input id="m_al" type="text"></div>
          <div class="col"><label>Takım B</label><input id="m_b" type="text"></div>
          <div class="col"><label>B Logo URL</label><input id="m_bl" type="text"></div>
          <div class="col"><label>Stadyum</label><input id="m_st" type="text"></div>
          <div class="col"><label>Maç Zamanı (UTC YYYY-MM-DD HH:MM:SS)</label><input id="m_time" type="text"></div>
          <div class="col"><label>Yayın URL</label><input id="m_stream" type="text"></div>
          <div class="col"><label>Aktif</label><select id="m_active"><option value="1">Evet</option><option value="0">Hayır</option></select></div>
        </div>
        <button class="btn primary" id="btn_add_match">Ekle</button>
        <table>
          <thead><tr><th>ID</th><th>A</th><th>B</th><th>Stadyum</th><th>Zaman</th><th>Aktif</th><th>İşlem</th></tr></thead>
          <tbody id="tbl_matches"></tbody>
        </table>
      </div>

      <button id="btn_logout" class="btn ghost right">Çıkış</button>
    </div>
  </div>

<script>
// diziportal admin js | Developer: DiziPortal.Com
const A = {
  me: '/api/me.php',
  login: '/api/login.php',
  logout: '/api/logout.php',
  settings: '/api/settings.php',
  upload: '/api/upload.php',
  channels: '/api/channels.php',
  matches: '/api/matches.php'
};
let CSRF = '<?php echo $csrf; ?>';

function qs(id){return document.getElementById(id)}
function td(v){return `<td>${v}</td>`}

function show(id, on){ qs(id).style.display = on? 'block':'none'; }

function refreshAuth(){
  fetch(A.me).then(r=>r.json()).then(d=>{
    CSRF = d.csrf || CSRF;
    show('login_card', !d.logged_in);
    show('dashboard', !!d.logged_in);
    if(d.logged_in){ loadAll(); }
  })
}

function login(){
  const body = { username: qs('l_user').value, password: qs('l_pass').value, csrf: CSRF };
  fetch(A.login, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)}).then(r=>r.json()).then(d=>{
    if(d.ok){ refreshAuth(); } else { alert(d.error||'Hata'); }
  })
}

function saveSettings(){
  const body = {
    csrf: CSRF,
    brand_name: qs('s_brand').value,
    player_logo_url: qs('s_logo').value,
    hero_title: qs('s_h1').value,
    hero_subtitle: qs('s_h2').value,
    telegram_url: qs('s_tg').value,
    instagram_url: qs('s_ig').value,
    twitter_url: qs('s_tw').value,
    tiktok_url: qs('s_tt').value,
  };
  fetch(A.settings, { method:'PUT', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)}).then(r=>r.json()).then(d=>{
    qs('s_msg').textContent = d.ok? 'Kaydedildi':'Hata';
  })
}

function loadSettings(){
  fetch(A.settings).then(r=>r.json()).then(s=>{
    qs('s_brand').value = s.brand_name||'';
    qs('s_logo').value = s.player_logo_url||'';
    qs('s_h1').value = s.hero_title||'';
    qs('s_h2').value = s.hero_subtitle||'';
    qs('s_tg').value = s.telegram_url||'';
    qs('s_ig').value = s.instagram_url||'';
    qs('s_tw').value = s.twitter_url||'';
    qs('s_tt').value = s.tiktok_url||'';
  })
}

function uploadFile(){
  const f = qs('up_file').files[0];
  if(!f) return;
  const fd = new FormData();
  fd.append('file', f);
  fd.append('csrf', CSRF);
  fetch(A.upload, { method:'POST', body: fd }).then(r=>r.json()).then(d=>{
    qs('up_msg').textContent = d.ok? d.url : (d.error||'Hata');
  })
}

function addChannel(){
  const body = {
    csrf: CSRF,
    name: qs('c_name').value,
    logo_url: qs('c_logo').value,
    stream_url: qs('c_stream').value,
    is_active: qs('c_active').value
  };
  fetch(A.channels, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)}).then(()=>loadChannels())
}

function loadChannels(){
  fetch(A.channels).then(r=>r.json()).then(arr=>{
    qs('tbl_channels').innerHTML = arr.map(c=>
      `<tr>${td(c.id)}${td(c.name)}${td(`<img src='${c.logo_url||''}' style='height:24px'>`)}${td(c.stream_url)}${td(c.is_active?'Evet':'Hayır')}<td class='actions'><button class='btn ghost' onclick='delChannel(${c.id})'>Sil</button></td></tr>`
    ).join('')
  })
}

function delChannel(id){
  fetch(A.channels+`?id=${id}&csrf=${encodeURIComponent(CSRF)}`, {method:'DELETE'}).then(()=>loadChannels())
}

function addMatch(){
  const body = {
    csrf: CSRF,
    team_a_name: qs('m_a').value,
    team_a_logo: qs('m_al').value,
    team_b_name: qs('m_b').value,
    team_b_logo: qs('m_bl').value,
    stadium: qs('m_st').value,
    match_time: qs('m_time').value,
    stream_url: qs('m_stream').value,
    is_active: qs('m_active').value
  };
  fetch(A.matches, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)}).then(()=>loadMatches())
}

function loadMatches(){
  fetch(A.matches).then(r=>r.json()).then(arr=>{
    qs('tbl_matches').innerHTML = arr.map(m=>
      `<tr>${td(m.id)}${td(m.team_a_name)}${td(m.team_b_name)}${td(m.stadium||'')}${td(m.match_time)}${td(m.is_active?'Evet':'Hayır')}<td class='actions'><button class='btn ghost' onclick='delMatch(${m.id})'>Sil</button></td></tr>`
    ).join('')
  })
}

function delMatch(id){
  fetch(A.matches+`?id=${id}&csrf=${encodeURIComponent(CSRF)}`, {method:'DELETE'}).then(()=>loadMatches())
}

function loadAll(){
  loadSettings();
  loadChannels();
  loadMatches();
}

document.getElementById('btn_login').addEventListener('click', login);
document.getElementById('btn_save_settings').addEventListener('click', saveSettings);
document.getElementById('btn_upload').addEventListener('click', uploadFile);
document.getElementById('btn_add_channel').addEventListener('click', addChannel);
document.getElementById('btn_add_match').addEventListener('click', addMatch);
document.getElementById('btn_logout').addEventListener('click', ()=>{ fetch(A.logout).then(()=>refreshAuth()) });

refreshAuth();
</script>
</body>
</html>