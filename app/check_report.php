
<?php

header('Content-Type: application/json; charset=utf-8');

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

// Ambil nomor tiket dari URL
$ticketNumber = trim($_GET['ticket_number'] ?? '');

// Validasi nomor tiket
if ($ticketNumber === '') {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Nomor tiket wajib diisi.'
    ]);

    exit;
}

try {

    // Cari laporan berdasarkan nomor tiket
    $sql = "SELECT
                ticket_number,
                category,
                status,
                created_at,
                updated_at
            FROM reports
            WHERE ticket_number = :ticket_number
            LIMIT 1";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':ticket_number' => $ticketNumber
    ]);

    $report = $stmt->fetch();

    // Jika laporan tidak ditemukan
    if (!$report) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Laporan tidak ditemukan.'
        ]);

        exit;
    }

    // Jika laporan ditemukan
    echo json_encode([
        'success' => true,
        'message' => 'Laporan berhasil ditemukan.',
        'data' => $report
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat mencari laporan.'
    ]);

}
