
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

// Ambil nomor tiket
$ticketNumber = trim($_GET['ticket_number'] ?? '');

if ($ticketNumber === '') {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Nomor tiket wajib diisi.'
    ]);

    exit;
}

try {

    // Pastikan laporan tersedia
    $checkSql = "SELECT id FROM reports
                 WHERE ticket_number = :ticket_number
                 LIMIT 1";

    $checkStmt = $pdo->prepare($checkSql);

    $checkStmt->execute([
        ':ticket_number' => $ticketNumber
    ]);

    $report = $checkStmt->fetch();

    if (!$report) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Laporan tidak ditemukan.'
        ]);

        exit;
    }

    // Ambil riwayat status laporan
    $historySql = "SELECT
                       old_status,
                       new_status,
                       note,
                       created_at
                   FROM report_updates
                   WHERE report_id = :report_id
                   ORDER BY created_at ASC";

    $historyStmt = $pdo->prepare($historySql);

    $historyStmt->execute([
        ':report_id' => $report['id']
    ]);

    $history = $historyStmt->fetchAll();

    echo json_encode([
        'success' => true,
        'message' => 'Riwayat laporan berhasil ditemukan.',
        'ticket_number' => $ticketNumber,
        'history' => $history
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat mengambil riwayat laporan.'
    ]);

}
