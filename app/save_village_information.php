
<?php

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
$category = trim($_POST['category'] ?? '');
$title = trim($_POST['title'] ?? '');
$value = trim($_POST['value'] ?? '');
$unit = trim($_POST['unit'] ?? '');
$description = trim($_POST['description'] ?? '');
$referenceYear = trim($_POST['reference_year'] ?? '');
$status = trim($_POST['status'] ?? 'Draft');
$displayOrder = $_POST['display_order'] ?? 0;

// Validasi kategori dan judul
if ($category === '' || $title === '') {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Kategori dan judul wajib diisi.'
    ]);
    exit;
}

// Validasi status
if (!in_array($status, ['Draft', 'Published'], true)) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Status informasi tidak valid.'
    ]);
    exit;
}

// Validasi tahun
if ($referenceYear !== '') {
    if (
        !ctype_digit($referenceYear) ||
        (int) $referenceYear < 1901 ||
        (int) $referenceYear > 2155
    ) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Tahun referensi tidak valid.'
        ]);
        exit;
    }
}

// Validasi urutan tampilan
if (
    filter_var($displayOrder, FILTER_VALIDATE_INT) === false ||
    (int) $displayOrder < 0
) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Urutan tampilan harus berupa angka positif.'
    ]);
    exit;
}

// Siapkan lokasi penyimpanan foto
$uploadDirectory = __DIR__ . '/../uploads/information/';

if (!is_dir($uploadDirectory)) {
    if (!mkdir($uploadDirectory, 0755, true)) {
        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'Folder penyimpanan foto gagal dibuat.'
        ]);
        exit;
    }
}

$imagePath = null;
$uploadedFilePath = null;

// Proses upload foto jika ada
if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

    $file = $_FILES['image'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Upload foto gagal. Silakan coba kembali.'
        ]);
        exit;
    }

    // Maksimal ukuran foto 5 MB
    if ($file['size'] > 5 * 1024 * 1024) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Ukuran foto maksimal 5 MB.'
        ]);
        exit;
    }

    // Periksa tipe file berdasarkan isi file, bukan nama file
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

    // Buat nama file acak agar tidak bentrok
    $fileName = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType];

    $destination = $uploadDirectory . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'Foto gagal disimpan ke server.'
        ]);
        exit;
    }

    $imagePath = 'uploads/information/' . $fileName;
    $uploadedFilePath = $destination;
}

try {

    $sql = "INSERT INTO village_information (
                category,
                title,
                value,
                unit,
                description,
                image,
                reference_year,
                status,
                display_order
            ) VALUES (
                :category,
                :title,
                :value,
                :unit,
                :description,
                :image,
                :reference_year,
                :status,
                :display_order
            )";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':category' => $category,
        ':title' => $title,
        ':value' => $value !== '' ? $value : null,
        ':unit' => $unit !== '' ? $unit : null,
        ':description' => $description !== '' ? $description : null,
        ':image' => $imagePath,
        ':reference_year' => $referenceYear !== ''
            ? (int) $referenceYear
            : null,
        ':status' => $status,
        ':display_order' => (int) $displayOrder
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Informasi desa berhasil ditambahkan.',
        'data' => [
            'id' => $pdo->lastInsertId(),
            'category' => $category,
            'title' => $title,
            'image' => $imagePath,
            'status' => $status
        ]
    ]);

} catch (PDOException $e) {

    // Hapus foto jika penyimpanan database gagal
    if ($uploadedFilePath !== null && file_exists($uploadedFilePath)) {
        unlink($uploadedFilePath);
    }

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat menyimpan informasi desa.'
    ]);
}
