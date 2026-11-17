
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/database.php';

// Pastikan request menggunakan GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Metode request tidak diizinkan.'
    ]);
    exit;
}

try {

    // Ambil filter kategori jika diberikan
    $category = trim($_GET['category'] ?? '');

    $sql = "SELECT
                id,
                category,
                title,
                value,
                unit,
                description,
                image,
                reference_year,
                status,
                display_order,
                created_at,
                updated_at
            FROM village_information";

    $params = [];

    if ($category !== '') {
        $sql .= " WHERE category = :category";
        $params[':category'] = $category;
    }

    $sql .= " ORDER BY display_order ASC, created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $information = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'message' => 'Daftar informasi desa berhasil diambil.',
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
