
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

// API hanya menerima GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Metode request tidak diizinkan.'
    ]);
    exit;
}

try {

    $stmt = $pdo->query(
        "SELECT
            id,
            phone,
            email,
            village_address,
            office_hours,
            website,
            instagram,
            facebook,
            map_url,
            updated_at
         FROM village_contact
         ORDER BY id ASC
         LIMIT 1"
    );

    $contact = $stmt->fetch();

    if (!$contact) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Informasi kontak desa belum tersedia.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Informasi kontak desa berhasil diambil.',
        'data' => $contact
    ]);

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat mengambil kontak desa.'
    ]);
}
