
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

// API ini hanya menerima GET
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
                category,
                title,
                value,
                unit,
                description,
                image,
                reference_year
            FROM village_information
            WHERE status = 'Published'";

    $params = [];

    if ($category !== '') {
        $sql .= " AND category = :category";
        $params[':category'] = $category;
    }

    $sql .= " ORDER BY display_order ASC, created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $information = $stmt->fetchAll();

    // Tambahkan URL lengkap untuk setiap foto
    $baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/Ecovillage/';

    foreach ($information as &$item) {

        $item['image_url'] = !empty($item['image'])
            ? $baseUrl . $item['image']
            : null;

    }

    unset($item);

    echo json_encode([
        'success' => true,
        'message' => 'Informasi desa berhasil diambil.',
        'total' => count($information),
        'data' => $information
    ]);

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat mengambil informasi desa.'
    ]);
}
