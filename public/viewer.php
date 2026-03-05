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
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($formName) ?> – <?= APP_NAME ?></title>
<link rel="stylesheet" href="/style.css">
<style>
  body { overflow: hidden; }
</style>
</head>
<body>
<div class="viewer-page">
  <!-- ── Top bar ─────────────────────────────────────────────────────────── -->
  <div class="viewer-topbar" id="topbar">
    <div style="flex:1;display:flex;align-items:center">
      <a href="/dashboard.php" class="btn btn-ghost btn-sm" style="gap:.4rem">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back
      </a>
    </div>

    <div class="viewer-form-name" style="justify-content:center;position:absolute;left:50%;transform:translateX(-50%);max-width:40%">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
        <defs><linearGradient id="fg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#6366f1"/><stop offset="100%" stop-color="#06b6d4"/></linearGradient></defs>
        <path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z" fill="url(#fg)"/>
      </svg>
      <span id="formTitle"><?= h($formName) ?></span>
    </div>

    <div style="flex:1;display:flex;justify-content:flex-end">
      <div class="viewer-timer" id="timer" aria-live="polite" aria-label="Time remaining">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
        </svg>
        <span id="timerDisplay">--:--</span>
      </div>
    </div>
  </div>

  <!-- ── Iframe ─────────────────────────────────────────────────────────── -->
  <div id="frameContainer" class="viewer-frame-mask" style="flex:1;display:flex;flex-direction:column">
    <iframe
      id="formIframe"
      class="viewer-iframe"
      src="<?= h($formUrl) ?>"
      allow="autoplay; camera; microphone"
      loading="eager"
      title="<?= h($formName) ?>"
    ></iframe>
  </div>

  <!-- ── Expired overlay ────────────────────────────────────────────────── -->
  <div id="expiredOverlay" class="viewer-expired" style="display:none">
    <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="1.5">
      <circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/>
    </svg>
    <h2>Time's Up!</h2>
    <p>Your session has expired. The form is no longer accessible.</p>
    <a href="/dashboard.php" class="btn btn-primary" style="margin-top:.5rem">Return to Dashboard</a>
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
</script>
</body>
</html>
