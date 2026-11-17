
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

// Ambil data laporan
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

// Pastikan foto diunggah
if (!isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Foto laporan wajib diunggah.'
    ]);
    exit;
}

$photo = $_FILES['photo'];

// Periksa error upload
if ($photo['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Foto gagal dikirim.'
    ]);
    exit;
}

// Maksimal ukuran foto 5 MB
if ($photo['size'] <= 0 || $photo['size'] > 5 * 1024 * 1024) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Ukuran foto harus lebih dari 0 dan maksimal 5 MB.'
    ]);
    exit;
}

// Periksa format foto berdasarkan MIME
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($photo['tmp_name']);

$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp'
];

if (!isset($allowedTypes[$mimeType])) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Format foto harus JPG, PNG, atau WEBP.'
    ]);
    exit;
}

// Buat nomor tiket
$ticketNumber = 'ECO-' . date('Ymd') . '-' .
    strtoupper(bin2hex(random_bytes(3)));

$savedFile = null;

try {

    $pdo->beginTransaction();

    // Simpan laporan
    $sql = "INSERT INTO reports
            (ticket_number, category, description, location, rt, rw)
            VALUES
            (:ticket_number, :category, :description, :location, :rt, :rw)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':ticket_number' => $ticketNumber,
        ':category' => $category,
        ':description' => $description,
        ':location' => $location,
        ':rt' => $rt !== '' ? $rt : null,
        ':rw' => $rw !== '' ? $rw : null
    ]);

    $reportId = $pdo->lastInsertId();

    // Siapkan folder upload
    $uploadDir = __DIR__ . '/../uploads/reports/';

    if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
        throw new RuntimeException('Folder upload tidak tersedia.');
    }

    // Buat nama file acak
    $fileName = bin2hex(random_bytes(16)) . '.' .
        $allowedTypes[$mimeType];

    $destination = $uploadDir . $fileName;

    // Pindahkan foto ke folder uploads
    if (!move_uploaded_file($photo['tmp_name'], $destination)) {
        throw new RuntimeException('Foto gagal disimpan.');
    }

    $savedFile = $destination;

    $photoPath = 'uploads/reports/' . $fileName;

    // Simpan lokasi foto ke database
    $photoStmt = $pdo->prepare(
        "UPDATE reports SET photo_path = :photo_path WHERE id = :id"
    );

    $photoStmt->execute([
        ':photo_path' => $photoPath,
        ':id' => $reportId
    ]);

    // Simpan riwayat status pertama
    $historySql = "INSERT INTO report_updates
                   (report_id, old_status, new_status, note)
                   VALUES
                   (:report_id, NULL, 'Diterima', 'Laporan berhasil dikirim.')";

    $historyStmt = $pdo->prepare($historySql);

    $historyStmt->execute([
        ':report_id' => $reportId
    ]);

    $pdo->commit();

    http_response_code(201);

    echo json_encode([
        'success' => true,
        'message' => 'Laporan berhasil dikirim.',
        'ticket_number' => $ticketNumber,
        'status' => 'Diterima',
        'photo_path' => $photoPath
    ]);

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Bersihkan foto jika penyimpanan database gagal
    if ($savedFile !== null && is_file($savedFile)) {
        unlink($savedFile);
    }

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat menyimpan laporan.'
    ]);
}
