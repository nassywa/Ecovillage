
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

$category = trim($_POST['category'] ?? '');
$title = trim($_POST['title'] ?? '');
$value = trim($_POST['value'] ?? '');
$unit = trim($_POST['unit'] ?? '');
$description = trim($_POST['description'] ?? '');
$referenceYear = trim($_POST['reference_year'] ?? '');
$status = trim($_POST['status'] ?? '');
$displayOrder = $_POST['display_order'] ?? null;

if (!$id || $id < 1 || $category === '' || $title === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID, kategori, dan judul wajib diisi dengan benar.'
    ]);
    exit;
}

if (!in_array($status, ['Draft', 'Published'], true)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Status informasi tidak valid.'
    ]);
    exit;
}

if (
    $referenceYear !== '' &&
    (
        !ctype_digit($referenceYear) ||
        (int) $referenceYear < 1901 ||
        (int) $referenceYear > 2155
    )
) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Tahun referensi tidak valid.'
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

// Ambil informasi lama untuk mengetahui lokasi foto sebelumnya
try {

    $check = $pdo->prepare(
        "SELECT id, image FROM village_information WHERE id = :id"
    );

    $check->execute([':id' => $id]);
    $oldInformation = $check->fetch();

    if (!$oldInformation) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Informasi desa tidak ditemukan.'
        ]);
        exit;
    }

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal memeriksa informasi desa.'
    ]);
    exit;
}

$oldImage = $oldInformation['image'];
$newImage = $oldImage;
$uploadedFilePath = null;

// Proses foto baru jika admin memilih file
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

    $newImage = 'uploads/information/' . $fileName;
    $uploadedFilePath = $destination;
}

try {

    $sql = "UPDATE village_information SET
                category = :category,
                title = :title,
                value = :value,
                unit = :unit,
                description = :description,
                image = :image,
                reference_year = :reference_year,
                status = :status,
                display_order = :display_order
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':category' => $category,
        ':title' => $title,
        ':value' => $value !== '' ? $value : null,
        ':unit' => $unit !== '' ? $unit : null,
        ':description' => $description !== '' ? $description : null,
        ':image' => $newImage,
        ':reference_year' => $referenceYear !== ''
            ? (int) $referenceYear
            : null,
        ':status' => $status,
        ':display_order' => (int) $displayOrder,
        ':id' => $id
    ]);

    // Hapus foto lama hanya setelah database berhasil diperbarui
    if (
        $uploadedFilePath !== null &&
        !empty($oldImage) &&
        strpos($oldImage, 'uploads/information/') === 0
    ) {
        $oldImagePath = __DIR__ . '/../' . $oldImage;

        if (is_file($oldImagePath)) {
            unlink($oldImagePath);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Informasi desa berhasil diperbarui.',
        'data' => [
            'id' => $id,
            'image' => $newImage
        ]
    ]);

} catch (PDOException $e) {

    // Bersihkan foto baru jika database gagal diperbarui
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
        'message' => 'Terjadi kesalahan saat memperbarui informasi desa.'
    ]);
}
