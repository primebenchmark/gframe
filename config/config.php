<?php
// ─── Application Configuration ────────────────────────────────────────────────
define('APP_NAME',    'GForm Portal');
define('APP_VERSION', '1.0.0');
define('BASE_PATH',   dirname(__DIR__));
define('DB_PATH',     BASE_PATH . '/database/gframe.sqlite');
define('SESSION_NAME','gframe_session');

// ─── Default Admin Credentials (change after first login) ───────────────────
define('DEFAULT_ADMIN_USER', 'admin');
define('DEFAULT_ADMIN_PASS', 'admin123');

// ─── Time Zone ───────────────────────────────────────────────────────────────
date_default_timezone_set('Asia/Tokyo');
