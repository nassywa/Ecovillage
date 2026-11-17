
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

$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['village_address'] ?? '');
$officeHours = trim($_POST['office_hours'] ?? '');
$website = trim($_POST['website'] ?? '');
$instagram = trim($_POST['instagram'] ?? '');
$facebook = trim($_POST['facebook'] ?? '');
$mapUrl = trim($_POST['map_url'] ?? '');

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Format email tidak valid.'
    ]);
    exit;
}

try {

    // Cek apakah data kontak sudah tersedia
    $check = $pdo->query(
        "SELECT id FROM village_contact ORDER BY id ASC LIMIT 1"
    );

    $existing = $check->fetch();

    if ($existing) {

        // Perbarui data yang sudah ada
        $sql = "UPDATE village_contact SET
                    phone = :phone,
                    email = :email,
                    village_address = :address,
                    office_hours = :office_hours,
                    website = :website,
                    instagram = :instagram,
                    facebook = :facebook,
                    map_url = :map_url
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':phone' => $phone ?: null,
            ':email' => $email ?: null,
            ':address' => $address ?: null,
            ':office_hours' => $officeHours ?: null,
            ':website' => $website ?: null,
            ':instagram' => $instagram ?: null,
            ':facebook' => $facebook ?: null,
            ':map_url' => $mapUrl ?: null,
            ':id' => $existing['id']
        ]);

        $message = 'Kontak desa berhasil diperbarui.';

    } else {

        // Simpan data kontak pertama kali
        $sql = "INSERT INTO village_contact (
                    phone,
                    email,
                    village_address,
                    office_hours,
                    website,
                    instagram,
                    facebook,
                    map_url
                ) VALUES (
                    :phone,
                    :email,
                    :address,
                    :office_hours,
                    :website,
                    :instagram,
                    :facebook,
                    :map_url
                )";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':phone' => $phone ?: null,
            ':email' => $email ?: null,
            ':address' => $address ?: null,
            ':office_hours' => $officeHours ?: null,
            ':website' => $website ?: null,
            ':instagram' => $instagram ?: null,
            ':facebook' => $facebook ?: null,
            ':map_url' => $mapUrl ?: null
        ]);

        $message = 'Kontak desa berhasil disimpan.';
    }

    echo json_encode([
        'success' => true,
        'message' => $message
    ]);

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat menyimpan kontak desa.'
    ]);
}
