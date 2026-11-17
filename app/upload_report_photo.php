
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/database.php';

// Hanya menerima request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Metode request tidak diizinkan.'
    ]);
    exit;
}

// Validasi ID laporan
$reportId = $_POST['report_id'] ?? '';

if (!ctype_digit((string) $reportId) || (int) $reportId < 1) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID laporan tidak valid.'
    ]);
    exit;
}

// Validasi file foto
if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Foto gagal dikirim.'
    ]);
    exit;
}

$file = $_FILES['photo'];

// Maksimal ukuran foto 5 MB
if ($file['size'] > 5 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Ukuran foto maksimal 5 MB.'
    ]);
    exit;
}

// Periksa format berdasarkan isi file
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);

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

// Pastikan laporan tersedia
$stmt = $pdo->prepare(
    "SELECT id FROM reports WHERE id = :id LIMIT 1"
);

$stmt->execute([':id' => $reportId]);

if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Laporan tidak ditemukan.'
    ]);
    exit;
}

try {
    $uploadDir = 'C:/xampp/htdocs/Ecovillage/uploads/reports/';

    if (!is_dir($uploadDir)) {
        throw new RuntimeException('Folder upload tidak ditemukan.');
    }

    // Buat nama file acak
    $fileName = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType];
    $destination = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Foto gagal disimpan.');
    }

    // Simpan lokasi foto ke database
    $photoPath = 'uploads/reports/' . $fileName;

    $updateStmt = $pdo->prepare(
        "UPDATE reports SET photo_path = :photo_path WHERE id = :id"
    );

    $updateStmt->execute([
        ':photo_path' => $photoPath,
        ':id' => $reportId
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Foto laporan berhasil diunggah.',
        'photo_path' => $photoPath
    ]);

} catch (Throwable $e) {
    if (isset($destination) && is_file($destination)) {
        unlink($destination);
    }

    http_response_code(500);
    echo json_encode([
    'success' => false,
    'message' => 'Terjadi kesalahan saat menyimpan foto.'
]);
}
