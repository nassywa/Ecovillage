
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

if (!$id || $id < 1) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID galeri tidak valid.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "SELECT image FROM village_gallery WHERE id = :id"
    );

    $stmt->execute([':id' => $id]);
    $gallery = $stmt->fetch();

    if (!$gallery) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Foto galeri tidak ditemukan.'
        ]);
        exit;
    }

    $delete = $pdo->prepare(
        "DELETE FROM village_gallery WHERE id = :id"
    );

    $delete->execute([':id' => $id]);

    // Hapus file foto setelah data database berhasil dihapus
    $image = $gallery['image'];

    if (
        !empty($image) &&
        strpos($image, 'uploads/gallery/') === 0
    ) {
        $imagePath = __DIR__ . '/../' . $image;

        if (is_file($imagePath)) {
            unlink($imagePath);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Foto galeri berhasil dihapus.'
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat menghapus galeri.'
    ]);
}
