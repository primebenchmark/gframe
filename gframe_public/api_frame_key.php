<?php
// One-time-use endpoint: returns the AES key material for a given nonce,
// then immediately destroys it so it cannot be replayed.
require_once __DIR__ . '/../gframe_src/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo '{}'; exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$nonce = $input['n'] ?? '';

if (!$nonce || empty($_SESSION['frame_nonces'][$nonce])) {
    http_response_code(403);
    echo '{}'; exit;
}

$data = $_SESSION['frame_nonces'][$nonce];
unset($_SESSION['frame_nonces'][$nonce]); // one-time use

echo json_encode([
    'k' => $data['k'],
    'i' => $data['i'],
    't' => $data['t'],
    'c' => $data['c'],
]);
