<?php
require_once __DIR__ . '/../src/bootstrap.php';
if (Auth::isAdmin()) { header('Location: /admin/'); exit; }
if (Auth::isStudent()) { header('Location: /dashboard.php'); exit; }

$role  = $_GET['role'] ?? 'student';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'student';
    if ($role === 'admin') {
        if (Auth::loginAdmin($username, $password)) {
            header('Location: /admin/'); exit;
        }
    } else {
        if (Auth::loginStudent($username, $password)) {
            header('Location: /dashboard.php'); exit;
        }
    }
    $error = 'Invalid username or password. Please try again.';
}

csrf(); // ensure token exists
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= getTheme() ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login – <?= APP_NAME ?></title>
<meta name="description" content="Secure login portal for GForm Portal – access your assigned Google Forms.">
<link rel="stylesheet" href="/style.css">
</head>
<body>
<div class="page-center" style="position:relative;z-index:1">
  <div class="login-box">
    <div class="login-logo">
      <svg width="48" height="48" viewBox="0 0 24 24" style="margin:0 auto .6rem;display:block">
        <defs><linearGradient id="g1" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#6366f1"/><stop offset="100%" stop-color="#06b6d4"/></linearGradient></defs>
        <rect width="24" height="24" rx="6" fill="url(#g1)"/>
        <path d="M19 7H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2zm-7 9l-4-4 1.4-1.4L12 13.2l5.6-5.6L19 9l-7 7z" fill="#fff"/>
      </svg>
      <h1><?= APP_NAME ?></h1>
      <p>Sign in to access your forms</p>
    </div>

    <div class="login-tabs" role="tablist">
      <button type="button" class="login-tab <?= $role==='student'?'active':'' ?>"
        onclick="setRole('student')" role="tab" aria-selected="<?= $role==='student'?'true':'false' ?>">
        Student
      </button>
      <button type="button" class="login-tab <?= $role==='admin'?'active':'' ?>"
        onclick="setRole('admin')" role="tab" aria-selected="<?= $role==='admin'?'true':'false' ?>">
        Admin
      </button>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger"><span>✕</span><?= h($error) ?></div>
    <?php endif; ?>

    <form method="post" id="loginForm" autocomplete="on">
      <input type="hidden" name="_csrf" value="<?= csrf() ?>">
      <input type="hidden" name="role" id="roleField" value="<?= h($role) ?>">

      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <input class="form-control" type="text" name="username" id="username"
               placeholder="Enter your username" required autofocus
               value="<?= h($_POST['username'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input class="form-control" type="password" name="password" id="password"
               placeholder="Enter your password" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:.75rem">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/>
        </svg>
        Sign In
      </button>
    </form>
  </div>
</div>
<script>
function setRole(r) {
  document.getElementById('roleField').value = r;
  document.querySelectorAll('.login-tab').forEach((t,i) => {
    t.classList.toggle('active', (i===0&&r==='student')||(i===1&&r==='admin'));
  });
}
</script>
</body>
</html>
