<?php
// ─── Core bootstrap ────────────────────────────────────────────────────────
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::start();

// Helper: render flash messages and clear them
function flash(?string $type = null, ?string $msg = null): string
{
    if ($msg !== null) {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
        return '';
    }
    if (isset($_SESSION['flash'])) {
        ['type' => $t, 'msg' => $m] = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $icon = $t === 'success' ? '✓' : ($t === 'danger' ? '✕' : 'ℹ');
        return "<div class='alert alert-{$t}'><span>{$icon}</span>" . htmlspecialchars($m) . "</div>";
    }
    return '';
}

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function csrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}
function checkCsrf(): void {
    if (($_POST['_csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
        http_response_code(403); die('Invalid CSRF token');
    }
}

function getTheme(): string
{
    if (isset($_SESSION['theme'])) return $_SESSION['theme'];
    if (isset($_COOKIE['theme'])) return $_COOKIE['theme'];
    return 'dark';
}

function navHtml(string $userName, bool $isAdmin = false): string {
    $role = $isAdmin ? 'Admin' : 'Student';
    $icon = $isAdmin
      ? '<path d="M12 2a5 5 0 1 1 0 10A5 5 0 0 1 12 2zm0 12c-7 0-9 3.5-9 5v1h18v-1c0-1.5-2-5-9-5z"/>'
      : '<path d="M12 12a6 6 0 1 0 0-12 6 6 0 0 0 0 12zm0 2c-4 0-8 2-8 4v2h16v-2c0-2-4-4-8-4z"/>';
    $home = $isAdmin ? '/admin/' : '/dashboard.php';
    $curTheme = getTheme();
    $themeIcon = $curTheme === 'dark' 
        ? '<path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m11.314 11.314l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>' 
        : '<path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>';

    $h = (int)Database::getSetting('footer_height', 155);

    return <<<HTML
<style>:root { --topbar-h: {$h}px; }</style>
<nav class="nav">
  <a href="{$home}" class="nav-brand">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" style="fill:url(#grd)">
      <defs><linearGradient id="grd" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#6366f1"/><stop offset="100%" stop-color="#06b6d4"/></linearGradient></defs>
      <path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm-7 14l-5-5 1.41-1.41L12 14.17l7.59-7.59L21 8l-9 9z"/>
    </svg>
    GForm Portal
  </a>
  <div class="nav-spacer"></div>

  <button class="btn-icon btn-ghost" onclick="toggleTheme()" title="Toggle Theme" style="margin-right:.5rem">
    <svg id="themeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{$themeIcon}</svg>
  </button>

  <div class="nav-user">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="var(--text-muted)">{$icon}</svg>
    <span>{$userName}</span>
    <span class="badge badge-info" style="font-size:.68rem">{$role}</span>
  </div>
  <form method="post" action="/logout.php" style="margin:0">
    <input type="hidden" name="_csrf" value="{$_SESSION['csrf']}">
    <button class="btn-logout">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
      Logout
    </button>
  </form>
</nav>

<script>
(function() {
  const theme = "{$curTheme}";
  document.documentElement.setAttribute('data-theme', theme);
})();

async function toggleTheme() {
  const html = document.documentElement;
  const current = html.getAttribute('data-theme') || 'dark';
  const next = current === 'dark' ? 'light' : 'dark';
  
  html.setAttribute('data-theme', next);
  
  // Update icon
  const icon = document.getElementById('themeIcon');
  if (next === 'dark') {
    icon.innerHTML = '<path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m11.314 11.314l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>';
  } else {
    icon.innerHTML = '<path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>';
  }

  try {
    await fetch('/api_theme.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ theme: next })
    });
  } catch(e) { console.error("Failed to save theme", e); }
}
</script>
HTML;
}
