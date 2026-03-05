<?php
require_once __DIR__ . '/../src/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
}
Auth::logout();
