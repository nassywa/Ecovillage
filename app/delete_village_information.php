
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

// Ambil ID informasi
$id = filter_var(
    $_POST['id'] ?? null,
    FILTER_VALIDATE_INT
);

// Validasi ID
if (!$id || $id < 1) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'ID informasi tidak valid.'
    ]);
    exit;
}

try {

    // Hapus informasi berdasarkan ID
    $sql = "DELETE FROM village_information WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':id' => $id
    ]);

    // Periksa apakah ada data yang berhasil dihapus
    if ($stmt->rowCount() === 0) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Informasi desa tidak ditemukan.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Informasi desa berhasil dihapus.'
    ]);

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat menghapus informasi desa.'
    ]);
}
