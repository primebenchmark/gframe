<?php
require_once __DIR__ . '/../src/bootstrap.php';
Auth::start();
if (Auth::isAdmin()) { header('Location: /admin/'); exit; }
if (Auth::isStudent()) { header('Location: /dashboard.php'); exit; }
header('Location: /login.php');
exit;
