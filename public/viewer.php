<?php
require_once __DIR__ . '/../src/bootstrap.php';
Auth::requireStudent();

$formId    = (int)($_GET['form'] ?? 0);
$studentId = (int)$_SESSION['student_id'];

if (!$formId) {
    header('Location: /dashboard.php'); exit;
}

$form = Database::getForm($formId);
if (!$form || !$form['active']) {
    header('Location: /dashboard.php'); exit;
}

$opens    = Database::countOpens($studentId, $formId);
$maxOpens = (int)$form['max_opens'];

if ($opens >= $maxOpens) {
    flash('danger', 'You have reached the maximum number of opens for this form.');
    header('Location: /dashboard.php'); exit;
}

// ── Log this open ──────────────────────────────────────────────────────────
Database::logOpen($studentId, $formId);

$formName = $form['name'];
$formUrl  = $form['url'];
$duration = (int)$form['duration']; // minutes
$seconds  = $duration * 60;
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

    <!-- ── Expired overlay ──────────────────────────────────────────────── -->
    <div id="expiredOverlay" class="viewer-expired" style="display:none">
      <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="1.5">
        <circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/>
      </svg>
      <h2>Time's Up!</h2>
      <p>Your session has expired. The form is no longer accessible.</p>
      <a href="/dashboard.php" class="btn btn-primary" style="margin-top:.5rem">Return to Dashboard</a>
    </div>
  </div>

  <!-- ── Bottom bar ────────────────────────────────────────────────────── -->
  <div class="viewer-topbar" id="topbar">
    <div class="viewer-topbar-row">
      <a href="/dashboard.php" class="btn btn-ghost" style="gap:.5rem">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back to Dashboard
      </a>

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
  const SECONDS = <?= $seconds ?>;
  let remaining  = SECONDS;
  const timerEl  = document.getElementById('timer');
  const dispEl   = document.getElementById('timerDisplay');
  const frameEl  = document.getElementById('formIframe');
  const overlayEl= document.getElementById('expiredOverlay');

  function pad(n) { return String(n).padStart(2, '0'); }

  function formatTime(s) {
    const m = Math.floor(s / 60);
    const sec = s % 60;
    return pad(m) + ':' + pad(sec);
  }

  function tick() {
    dispEl.textContent = formatTime(remaining);

    // Colour transitions
    if (remaining <= 60) {
      timerEl.className = 'viewer-timer danger';
    } else if (remaining <= 300) {
      timerEl.className = 'viewer-timer warning';
    }

    if (remaining <= 0) {
      clearInterval(interval);
      expireForm();
      return;
    }
    remaining--;
  }

  function expireForm() {
    frameEl.src = 'about:blank';
    document.getElementById('frameContainer').style.display = 'none';
    overlayEl.style.display = 'flex';
    document.title = 'Time Expired – <?= addslashes(APP_NAME) ?>';
    dispEl.textContent = '00:00';
  }

  tick(); // Show immediately
  const interval = setInterval(tick, 1000);
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
