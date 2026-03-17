<?php
require_once __DIR__ . '/../gframe_src/bootstrap.php';

$formId = (int)($_GET['form'] ?? 0);

if (!$formId) {
    header('Location: /'); exit;
}

$form = Database::getForm($formId);
if (!$form || !$form['active']) {
    header('Location: /'); exit;
}

$formName = $form['name'];
$formUrl  = $form['url'];
csrf();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= getTheme() ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<title><?= h($formName) ?> – <?= APP_NAME ?></title>
<link rel="stylesheet" href="/style.css">
<style>
  :root { --topbar-h: <?= (int)Database::getSetting('footer_height', 155) ?>px; }
  body { overflow: hidden; }
</style>
</head>
<body>
<div class="viewer-page">
  <!-- ── Iframe ─────────────────────────────────────────────────────────── -->
  <div class="viewer-iframe-wrap">
    <div id="frameContainer" style="flex:1;display:flex;flex-direction:column;height:100%">
      <iframe
        id="formIframe"
        class="viewer-iframe"
        src="<?= h($formUrl) ?>"
        allow="autoplay; camera; microphone"
        loading="eager"
        title="<?= h($formName) ?>"
      ></iframe>
    </div>

  </div>

  <!-- ── Bottom bar ────────────────────────────────────────────────────── -->
  <div class="viewer-topbar" id="topbar">
    <div class="viewer-topbar-row">
      <a href="/" class="btn btn-ghost" style="gap:.5rem">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back to Dashboard
      </a>

      <button id="themeBtn" class="btn btn-ghost" style="gap:.5rem" aria-label="Toggle theme">
        <svg id="themeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
        </svg>
        <span id="themeLabel">Light</span>
      </button>

      <button id="fullscreenBtn" class="btn btn-ghost" style="gap:.5rem">
        <svg id="fsIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/>
        </svg>
        <span id="fsLabel">Fullscreen</span>
      </button>

      <div class="viewer-timer" id="timer" aria-live="polite" aria-label="Time remaining">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
        </svg>
        <span id="timerDisplay" style="font-size: 1.1rem">--:--</span>
      </div>
    </div>

    <div class="viewer-form-name">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
        <defs><linearGradient id="fg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#6366f1"/><stop offset="100%" stop-color="#06b6d4"/></linearGradient></defs>
        <path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z" fill="url(#fg)"/>
      </svg>
      <span id="formTitle"><?= h($formName) ?></span>
    </div>
  </div>
</div>

<script>
(function () {
  let elapsed   = 0;
  const timerEl = document.getElementById('timer');
  const dispEl  = document.getElementById('timerDisplay');

  function pad(n) { return String(n).padStart(2, '0'); }

  function formatTime(s) {
    const m = Math.floor(s / 60);
    const sec = s % 60;
    return pad(m) + ':' + pad(sec);
  }

  function tick() {
    dispEl.textContent = formatTime(elapsed);

    // Warn after one hour has passed
    if (elapsed >= 3600) {
      timerEl.className = 'viewer-timer warning';
    }

    elapsed++;
  }

  tick(); // Show immediately
  setInterval(tick, 1000);
})();

// ── Fullscreen toggle ────────────────────────────────────────────────────────
(function () {
  const btn   = document.getElementById('fullscreenBtn');
  const icon  = document.getElementById('fsIcon');
  const label = document.getElementById('fsLabel');

  const ENTER_PATH = 'M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3';
  const EXIT_PATH  = 'M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3';

  btn.addEventListener('click', function () {
    if (!document.fullscreenElement && !document.webkitFullscreenElement) {
      const el = document.documentElement;
      if (el.requestFullscreen) el.requestFullscreen();
      else if (el.webkitRequestFullscreen) el.webkitRequestFullscreen();
    } else {
      if (document.exitFullscreen) document.exitFullscreen();
      else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
    }
  });

  function onFullscreenChange() {
    const isFs = !!(document.fullscreenElement || document.webkitFullscreenElement);
    icon.querySelector('path').setAttribute('d', isFs ? EXIT_PATH : ENTER_PATH);
    label.textContent = isFs ? 'Exit Fullscreen' : 'Fullscreen';
  }

  document.addEventListener('fullscreenchange', onFullscreenChange);
  document.addEventListener('webkitfullscreenchange', onFullscreenChange);
})();

// ── Theme toggle ─────────────────────────────────────────────────────────────
(function () {
  const btn   = document.getElementById('themeBtn');
  const icon  = document.getElementById('themeIcon');
  const label = document.getElementById('themeLabel');
  const html  = document.documentElement;

  const MOON_PATH = 'M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z';
  const SUN_PATH  = 'M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42M12 5a7 7 0 1 0 0 14A7 7 0 0 0 12 5z';

  function applyTheme(theme) {
    html.setAttribute('data-theme', theme);
    if (theme === 'light') {
      icon.querySelector('path').setAttribute('d', SUN_PATH);
      label.textContent = 'Dark';
    } else {
      icon.querySelector('path').setAttribute('d', MOON_PATH);
      label.textContent = 'Light';
    }
  }

  // Initialise button state from current theme
  applyTheme(html.getAttribute('data-theme') || 'dark');

  btn.addEventListener('click', function () {
    const next = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
    applyTheme(next);
    fetch('/api_theme.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ theme: next })
    });
  });
})();

// ── Block zoom & copy shortcuts ─────────────────────────────────────────────
(function () {
  // Block right-click context menu on the parent page
  document.addEventListener('contextmenu', e => e.preventDefault());

  // Block copy and zoom via keyboard buttons
  document.addEventListener('keydown', function (e) {
    const ctrl = e.ctrlKey || e.metaKey;
    // Block copy (Ctrl+C)
    if (ctrl && e.key === 'c') { e.preventDefault(); return; }
    // Block zoom shortcuts: Ctrl +, Ctrl -, Ctrl 0
    if (ctrl && (e.key === '+' || e.key === '-' || e.key === '=' || e.key === '0')) {
      e.preventDefault();
    }
  });

  // Block wheel-based zoom (Ctrl + scroll)
  document.addEventListener('wheel', function (e) {
    if (e.ctrlKey) e.preventDefault();
  }, { passive: false });

  // Block pinch-to-zoom (Safari/iOS)
  document.addEventListener('gesturestart', e => e.preventDefault());
})();
</script>
</body>
</html>
