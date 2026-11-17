
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Metode request tidak diizinkan.'
    ]);
    exit;
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$category = trim($_POST['category'] ?? '');
$status = trim($_POST['status'] ?? '');
$displayOrder = $_POST['display_order'] ?? null;

if (!$id || $id < 1 || $title === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID dan judul wajib diisi.'
    ]);
    exit;
}

if (!in_array($status, ['Draft', 'Published'], true)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Status galeri tidak valid.'
    ]);
    exit;
}

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

// Ambil data lama
$check = $pdo->prepare(
    "SELECT id, image FROM village_gallery WHERE id = :id"
);

$check->execute([':id' => $id]);
$oldGallery = $check->fetch();

if (!$oldGallery) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Foto galeri tidak ditemukan.'
    ]);
    exit;
}

$oldImage = $oldGallery['image'];
$newImage = $oldImage;
$uploadedFilePath = null;

// Proses foto baru jika dipilih
if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

    $file = $_FILES['image'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Upload foto gagal.'
        ]);
        exit;
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Ukuran foto maksimal 5 MB.'
        ]);
        exit;
    }

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

    $uploadDirectory = __DIR__ . '/../uploads/gallery/';

    if (!is_dir($uploadDirectory)) {
        if (!mkdir($uploadDirectory, 0755, true)) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Folder penyimpanan galeri gagal dibuat.'
            ]);
            exit;
        }
    }

    $fileName = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType];

    $destination = $uploadDirectory . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Foto baru gagal disimpan.'
        ]);
        exit;
    }

    $newImage = 'uploads/gallery/' . $fileName;
    $uploadedFilePath = $destination;
}

try {

    $sql = "UPDATE village_gallery SET
                title = :title,
                description = :description,
                category = :category,
                image = :image,
                status = :status,
                display_order = :display_order
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':title' => $title,
        ':description' => $description !== '' ? $description : null,
        ':category' => $category !== '' ? $category : null,
        ':image' => $newImage,
        ':status' => $status,
        ':display_order' => (int) $displayOrder,
        ':id' => $id
    ]);

    // Hapus foto lama setelah database berhasil diperbarui
    if (
        $uploadedFilePath !== null &&
        !empty($oldImage) &&
        strpos($oldImage, 'uploads/gallery/') === 0
    ) {
        $oldImagePath = __DIR__ . '/../' . $oldImage;

        if (is_file($oldImagePath)) {
            unlink($oldImagePath);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Foto galeri berhasil diperbarui.',
        'data' => [
            'id' => $id,
            'image' => $newImage
        ]
    ]);

} catch (PDOException $e) {

    if (
        $uploadedFilePath !== null &&
        file_exists($uploadedFilePath)
    ) {
        unlink($uploadedFilePath);
    }

    error_log($e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat memperbarui galeri.'
    ]);
}
