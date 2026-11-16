
<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

// Pastikan admin sudah login
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Admin belum login.'
    ]);

    exit;
}

// Hapus seluruh data session
$_SESSION = [];

// Hapus cookie session dari browser
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Hancurkan session
session_destroy();

echo json_encode([
    'success' => true,
    'message' => 'Logout berhasil.'
]);
