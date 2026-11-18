
<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/database.php';

// Pastikan request menggunakan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Metode request tidak diizinkan.'
    ]);

    exit;
}

// Ambil data dari request
$reportId = $_POST['report_id'] ?? '';
$newStatus = trim($_POST['status'] ?? '');
$note = trim($_POST['note'] ?? '');

// Validasi ID laporan
if (!ctype_digit((string) $reportId) || (int) $reportId < 1) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'ID laporan tidak valid.'
    ]);

    exit;
}

// Daftar status yang diperbolehkan
$allowedStatuses = [
    'Diterima',
    'Diproses',
    'Diteruskan',
    'Selesai'
];

if (!in_array($newStatus, $allowedStatuses, true)) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Status laporan tidak valid.'
    ]);

    exit;
}

try {

    $pdo->beginTransaction();

    // Ambil status laporan saat ini
    $stmt = $pdo->prepare(
        "SELECT status FROM reports WHERE id = :id FOR UPDATE"
    );

    $stmt->execute([
        ':id' => $reportId
    ]);

    $report = $stmt->fetch();

    if (!$report) {
        $pdo->rollBack();

        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Laporan tidak ditemukan.'
        ]);

        exit;
    }

    $oldStatus = $report['status'];

    // Perbarui status dan catatan admin
    $updateStmt = $pdo->prepare(
        "UPDATE reports
         SET status = :status,
             admin_note = :admin_note
         WHERE id = :id"
    );

    $updateStmt->execute([
        ':status' => $newStatus,
        ':admin_note' => $note !== '' ? $note : null,
        ':id' => $reportId
    ]);

    // Simpan riwayat perubahan status
    $historyStmt = $pdo->prepare(
        "INSERT INTO report_updates
        (report_id, old_status, new_status, note)
        VALUES (:report_id, :old_status, :new_status, :note)"
    );

    $historyStmt->execute([
        ':report_id' => $reportId,
        ':old_status' => $oldStatus,
        ':new_status' => $newStatus,
        ':note' => $note !== '' ? $note : null
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Status dan catatan laporan berhasil diperbarui.',
        'old_status' => $oldStatus,
        'new_status' => $newStatus
    ]);

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat memperbarui status.'
    ]);

}
