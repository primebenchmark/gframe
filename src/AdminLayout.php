<?php
// Shared admin layout helper
function adminLayout(string $page, string $title, string $content): void {
    $pages = [
        'dashboard'=> ['icon'=>'<path d="M3 12l2-2m0 0 7-7 7 7M5 10v10a1 1 0 0 0 1 1h3m10-11 2 2m-2-2v10a1 1 0 0 0-1 1h-3m-6 0a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1m6 0h-6"/>', 'label'=>'Dashboard', 'href'=>'/admin/'],
        'forms'    => ['icon'=>'<path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>', 'label'=>'Forms', 'href'=>'/admin/forms.php'],
        'students' => ['icon'=>'<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm14 10v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>', 'label'=>'Students', 'href'=>'/admin/students.php'],
        'log'      => ['icon'=>'<path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>', 'label'=>'Access Log', 'href'=>'/admin/log.php'],
    ];
    $appName = APP_NAME;
    $nav = navHtml(htmlspecialchars($_SESSION['admin_user']), true);
    $sidebar = '';
    foreach ($pages as $k => $p) {
        $active = $k === $page ? 'active' : '';
        $sidebar .= "<a href='{$p['href']}' class='sidebar-link {$active}'>";
        $sidebar .= "<svg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'>{$p['icon']}</svg>";
        $sidebar .= "<span>{$p['label']}</span></a>";
    }
    echo <<<HTML
<!DOCTYPE html>
<html lang="en" data-theme="<?= getTheme() ?>">
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
