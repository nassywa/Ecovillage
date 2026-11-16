
<?php

// Mulai session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Periksa apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {

    http_response_code(401);

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => false,
        'message' => 'Akses ditolak. Silakan login terlebih dahulu.'
    ]);

    exit;
}
