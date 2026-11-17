
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

// Ambil ID laporan dari URL
$reportId = $_GET['id'] ?? '';

// Validasi ID laporan
if (!ctype_digit((string) $reportId) || (int) $reportId < 1) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'ID laporan tidak valid.'
    ]);
    exit;
}

try {

    // Ambil detail laporan
    $sql = "SELECT
                id,
                ticket_number,
                category,
                description,
                location,
                rt,
                rw,
                photo_path,
                status,
                admin_note,
                created_at,
                updated_at
            FROM reports
            WHERE id = :id
            LIMIT 1";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':id' => $reportId
    ]);

    $report = $stmt->fetch();

    // Periksa apakah laporan ditemukan
    if (!$report) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Laporan tidak ditemukan.'
        ]);
        exit;
    }

    // Buat URL foto jika tersedia
    $report['photo_url'] = null;

    if (!empty($report['photo_path'])) {
        $report['photo_url'] = 'http://' . $_SERVER['HTTP_HOST']
            . '/Ecovillage/' . $report['photo_path'];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Detail laporan berhasil ditemukan.',
        'data' => $report
    ]);

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat mengambil detail laporan.'
    ]);
}
