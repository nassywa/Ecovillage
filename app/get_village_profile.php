
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

// Pastikan request menggunakan GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Metode request tidak diizinkan.'
    ]);
    exit;
}

try {

    // Ambil profil desa
    $sql = "SELECT
                id,
                village_name,
                district,
                regency,
                province,
                description,
                history,
                vision,
                mission,
                geographic_info,
                village_address,
                village_logo,
                updated_at
            FROM village_profile
            ORDER BY id ASC
            LIMIT 1";

    $stmt = $pdo->query($sql);

    $profile = $stmt->fetch();

    // Periksa apakah profil sudah tersedia
    if (!$profile) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Profil desa belum tersedia.'
        ]);
        exit;
    }

    // Buat URL logo jika tersedia
    $profile['village_logo_url'] = null;

    if (!empty($profile['village_logo'])) {
        $profile['village_logo_url'] = 'http://' .
            $_SERVER['HTTP_HOST'] . '/Ecovillage/' .
            $profile['village_logo'];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Profil desa berhasil ditemukan.',
        'data' => $profile
    ]);

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat mengambil profil desa.'
    ]);
}
