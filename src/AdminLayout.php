<?php
// Shared admin layout helper
function adminLayout(string $page, string $title, string $content): void {
    $pages = [
        'dashboard'=> ['icon'=>'<path d="M3 12l2-2m0 0 7-7 7 7M5 10v10a1 1 0 0 0 1 1h3m10-11 2 2m-2-2v10a1 1 0 0 0-1 1h-3m-6 0a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1m6 0h-6"/>', 'label'=>'Dashboard', 'href'=>'/admin/'],
        'forms'    => ['icon'=>'<path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>', 'label'=>'Forms', 'href'=>'/admin/forms.php'],
        'settings' => ['icon'=>'<path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>', 'label'=>'Settings', 'href'=>'/admin/settings.php'],
    ];
    $appName = APP_NAME;
    $theme = getTheme();
    $nav = navHtml();
    $sidebar = '';
    foreach ($pages as $k => $p) {
        $active = $k === $page ? 'active' : '';
        $sidebar .= "<a href='{$p['href']}' class='sidebar-link {$active}'>";
        $sidebar .= "<svg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'>{$p['icon']}</svg>";
        $sidebar .= "<span>{$p['label']}</span></a>";
    }
    echo <<<HTML
<!DOCTYPE html>
<html lang="en" data-theme="{$theme}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$title} – Admin – {$appName}</title>
<link rel="stylesheet" href="/style.css">
</head>
<body>
{$nav}
<div class="admin-layout">
  <aside class="admin-sidebar">
    <div class="sidebar-label">Navigation</div>
    {$sidebar}
  </aside>
  <main class="admin-main">
    {$content}
  </main>
</div>
</body>
</html>
HTML;
}
