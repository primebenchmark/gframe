<?php
require_once __DIR__ . '/../src/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $theme = $data['theme'] ?? 'dark';
    
    // Save to cookie (for guest/unauthenticated state)
    setcookie('theme', $theme, [
        'expires' => time() + (86400 * 365),
        'path' => '/',
        'samesite' => 'Lax'
    ]);
    
    // Save to DB if logged in
    $id = null;
    $role = null;
    
    if (Auth::isAdmin()) {
        $id = $_SESSION['admin_id'];
        $role = 'admin';
    } elseif (Auth::isStudent()) {
        $id = $_SESSION['student_id'];
        $role = 'student';
    }
    
    if ($id && $role) {
        Database::updateTheme($role, $id, $theme);
        $_SESSION['theme'] = $theme;
    }

    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
