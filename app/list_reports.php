
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

    // Ambil seluruh laporan dari database
    $sql = "SELECT
                id,
                ticket_number,
                category,
                description,
                location,
                rt,
                rw,
                status,
                created_at,
                updated_at
            FROM reports
            ORDER BY created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $reports = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'message' => 'Daftar laporan berhasil diambil.',
        'total' => count($reports),
        'data' => $reports
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat mengambil daftar laporan.'
    ]);

}
