
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/database.php';

// Hanya menerima GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Metode request tidak diizinkan.'
    ]);
    exit;
}

try {

    $sql = "SELECT
                id,
                title,
                description,
                category,
                image,
                status,
                display_order,
                created_at
            FROM village_gallery
            ORDER BY display_order ASC, created_at DESC";

    $stmt = $pdo->query($sql);

    $gallery = $stmt->fetchAll();

    // Buat URL lengkap untuk foto
    $baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/Ecovillage/';

    foreach ($gallery as &$item) {

        $item['image_url'] = !empty($item['image'])
            ? $baseUrl . $item['image']
            : null;

    }

    unset($item);

    echo json_encode([
        'success' => true,
        'message' => 'Daftar galeri berhasil diambil.',
        'total' => count($gallery),
        'data' => $gallery
    ]);

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat mengambil daftar galeri.'
    ]);
}
