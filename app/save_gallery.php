
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/database.php';

// Hanya menerima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Metode request tidak diizinkan.'
    ]);
    exit;
}

// Ambil data formulir
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$category = trim($_POST['category'] ?? '');
$status = trim($_POST['status'] ?? 'Draft');
$displayOrder = $_POST['display_order'] ?? 0;

// Validasi judul
if ($title === '') {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Judul foto wajib diisi.'
    ]);
    exit;
}

// Validasi status
if (!in_array($status, ['Draft', 'Published'], true)) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Status galeri tidak valid.'
    ]);
    exit;
}

// Validasi urutan tampilan
if (
    filter_var($displayOrder, FILTER_VALIDATE_INT) === false ||
    (int) $displayOrder < 0
) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Urutan tampilan tidak valid.'
    ]);
    exit;
}

// Foto wajib diunggah
if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Foto galeri wajib diunggah.'
    ]);
    exit;
}

$file = $_FILES['image'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Upload foto gagal.'
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

// Validasi tipe file berdasarkan MIME
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

// Siapkan folder penyimpanan
$uploadDirectory = __DIR__ . '/../uploads/gallery/';

if (!is_dir($uploadDirectory)) {
    if (!mkdir($uploadDirectory, 0755, true)) {
        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'Folder galeri gagal dibuat.'
        ]);
        exit;
    }
}

// Buat nama file acak
$fileName = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType];

$destination = $uploadDirectory . $fileName;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Foto gagal disimpan.'
    ]);
    exit;
}

$imagePath = 'uploads/gallery/' . $fileName;

try {

    $sql = "INSERT INTO village_gallery (
                title,
                description,
                category,
                image,
                status,
                display_order
            ) VALUES (
                :title,
                :description,
                :category,
                :image,
                :status,
                :display_order
            )";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':title' => $title,
        ':description' => $description !== '' ? $description : null,
        ':category' => $category !== '' ? $category : null,
        ':image' => $imagePath,
        ':status' => $status,
        ':display_order' => (int) $displayOrder
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Foto galeri berhasil ditambahkan.',
        'data' => [
            'id' => $pdo->lastInsertId(),
            'title' => $title,
            'image' => $imagePath,
            'status' => $status
        ]
    ]);

} catch (PDOException $e) {

    // Hapus foto jika database gagal menyimpan data
    if (file_exists($destination)) {
        unlink($destination);
    }

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat menyimpan foto galeri.'
    ]);
}
