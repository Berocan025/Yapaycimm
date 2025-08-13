<?php
// diziportal - Public SPA for DG SPORTS
// Developer: DiziPortal.Com
require_once __DIR__ . '/../includes/init_db.php';
require_once __DIR__ . '/../includes/utils.php';
$cfg = app_config();
?><!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo htmlspecialchars($cfg['app_name']); ?> - Canlı Maçlar ve 7/24 Kanallar</title>
  <meta name="description" content="DG SPORTS - Canlı maç izle, 7/24 spor kanalları. Geliştirici: DiziPortal.Com" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/styles.css?v=<?php echo time(); ?>">
</head>
<body>
  <header class="dp-header">
    <div class="container">
      <div class="brand">
        <span class="logo-dot"></span>
        <span class="brand-name">DG SPORTS</span>
        <span class="dev">diziportal</span>
      </div>
      <nav class="socials">
        <a id="tg_link" href="#" target="_blank" aria-label="Telegram"><img src="/assets/icons/telegram.svg" alt="Telegram"/></a>
        <a id="ig_link" href="#" target="_blank" aria-label="Instagram"><img src="/assets/icons/instagram.svg" alt="Instagram"/></a>
        <a id="tw_link" href="#" target="_blank" aria-label="Twitter"><img src="/assets/icons/twitter.svg" alt="Twitter"/></a>
        <a id="tt_link" href="#" target="_blank" aria-label="TikTok"><img src="/assets/icons/tiktok.svg" alt="TikTok"/></a>
      </nav>
    </div>
  </header>

  <section class="hero">
    <div class="container">
      <h1 id="hero_title">Canlı Maçlar ve 7/24 Spor Kanalları</h1>
      <p id="hero_subtitle">DG SPORTS ile her zaman, her yerde. Geliştirici: DiziPortal.Com</p>
      <div class="hero-actions">
        <a href="#matches" class="btn primary">Maçlar</a>
        <a href="#channels" class="btn ghost">7/24 Kanallar</a>
      </div>
    </div>
  </section>

  <main class="container main">
    <section id="matches" class="card-section">
      <div class="section-head">
        <h2>Canlı Maçlar</h2>
        <span class="hint">Anlık izleyici sayıları ile</span>
      </div>
      <div id="matches_list" class="grid matches-grid"></div>
    </section>

    <section id="channels" class="card-section">
      <div class="section-head">
        <h2>7/24 Kanallar</h2>
        <span class="hint">HD yayınlar</span>
      </div>
      <div id="channels_list" class="grid channels-grid"></div>
    </section>

    <section id="contact" class="contact">
      <h2>İletişim</h2>
      <p>Bizimle sosyal medya üzerinden iletişime geçebilirsiniz.</p>
      <div class="social-links">
        <a id="c_tg" href="#" target="_blank">Telegram</a>
        <a id="c_ig" href="#" target="_blank">Instagram</a>
        <a id="c_tw" href="#" target="_blank">Twitter</a>
        <a id="c_tt" href="#" target="_blank">TikTok</a>
      </div>
    </section>
  </main>

  <footer class="dp-footer">
    <div class="container">
      <span>© <span id="brand_year"></span> DG SPORTS</span>
      <span>Geliştirici: DiziPortal.Com</span>
    </div>
  </footer>

  <div id="player_modal" class="modal hidden" role="dialog" aria-modal="true">
    <div class="modal-content">
      <button class="modal-close" id="modal_close" aria-label="Kapat">×</button>
      <div id="player_container" class="player-wrap"></div>
      <div class="player-meta">
        <div class="meta-left">
          <img id="meta_logo" src="" alt=""/>
          <div>
            <div id="meta_title" class="meta-title"></div>
            <div id="meta_sub" class="meta-sub"></div>
          </div>
        </div>
        <div class="meta-right">
          <span id="live_viewers" class="badge">0 izleyici</span>
        </div>
      </div>
    </div>
  </div>

  <!-- diziportal js -->
  <script src="https://playerjs.com/playerjs.js"></script>
  <script src="/assets/app.js?v=<?php echo time(); ?>"></script>
</body>
</html>