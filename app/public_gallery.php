
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

// API hanya menerima GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Metode request tidak diizinkan.'
    ]);
    exit;
}

try {

    $category = trim($_GET['category'] ?? '');

    $sql = "SELECT
                id,
                title,
                description,
                category,
                image,
                display_order,
                created_at
            FROM village_gallery
            WHERE status = 'Published'";

    $params = [];

    // Filter berdasarkan kategori jika diminta
    if ($category !== '') {
        $sql .= " AND category = :category";
        $params[':category'] = $category;
    }

    $sql .= " ORDER BY display_order ASC, created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $gallery = $stmt->fetchAll();

    // Buat URL lengkap foto
    $baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/Ecovillage/';

    foreach ($gallery as &$item) {

        $item['image_url'] = !empty($item['image'])
            ? $baseUrl . $item['image']
            : null;

    }

    unset($item);

    echo json_encode([
        'success' => true,
        'message' => 'Galeri desa berhasil diambil.',
        'total' => count($gallery),
        'data' => $gallery
    ]);

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat mengambil galeri desa.'
    ]);
}
