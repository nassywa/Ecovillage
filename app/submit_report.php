
<?php

header('Content-Type: application/json; charset=utf-8');

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

// Ambil data dari formulir
$category = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');
$location = trim($_POST['location'] ?? '');
$rt = trim($_POST['rt'] ?? '');
$rw = trim($_POST['rw'] ?? '');

// Validasi data wajib
if ($category === '' || $description === '' || $location === '') {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Kategori, deskripsi, dan lokasi wajib diisi.'
    ]);

    exit;
}

// Buat nomor tiket unik
$ticketNumber = 'ECO-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

try {

    // Simpan laporan ke database
    $sql = "INSERT INTO reports
            (ticket_number, category, description, location, rt, rw)
            VALUES
            (:ticket_number, :category, :description, :location, :rt, :rw)";

    $stmt = $pdo->prepare($sql);

    $pdo->beginTransaction();

// Simpan laporan utama
$stmt->execute([
    ':ticket_number' => $ticketNumber,
    ':category' => $category,
    ':description' => $description,
    ':location' => $location,
    ':rt' => $rt !== '' ? $rt : null,
    ':rw' => $rw !== '' ? $rw : null
]);

// Ambil ID laporan yang baru dibuat
$reportId = $pdo->lastInsertId();

// Simpan riwayat status pertama
$historySql = "INSERT INTO report_updates
               (report_id, old_status, new_status, note)
               VALUES (:report_id, NULL, 'Diterima', 'Laporan berhasil dikirim.')";

$historyStmt = $pdo->prepare($historySql);

$historyStmt->execute([
    ':report_id' => $reportId
]);

// Simpan kedua proses sekaligus
$pdo->commit();

    http_response_code(201);

    echo json_encode([
        'success' => true,
        'message' => 'Laporan berhasil dikirim.',
        'ticket_number' => $ticketNumber,
        'status' => 'Diterima'
    ]);

} catch (PDOException $e) {
    
    if ($pdo->inTransaction()) {
    $pdo->rollBack();
}

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat menyimpan laporan.'
    ]);

}
