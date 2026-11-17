
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

// Ambil data profil
$villageName = trim($_POST['village_name'] ?? '');
$district = trim($_POST['district'] ?? '');
$regency = trim($_POST['regency'] ?? '');
$province = trim($_POST['province'] ?? '');
$description = trim($_POST['description'] ?? '');
$history = trim($_POST['history'] ?? '');
$vision = trim($_POST['vision'] ?? '');
$mission = trim($_POST['mission'] ?? '');
$geographicInfo = trim($_POST['geographic_info'] ?? '');
$villageAddress = trim($_POST['village_address'] ?? '');

// Validasi nama desa
if ($villageName === '') {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Nama desa wajib diisi.'
    ]);
    exit;
}

$newFilePath = null;

try {

    // Ambil profil yang sudah tersedia
    $check = $pdo->query(
        "SELECT id, village_logo
         FROM village_profile
         ORDER BY id ASC
         LIMIT 1"
    );

    $existingProfile = $check->fetch();

    // Ambil logo lama agar tidak hilang saat profil diperbarui
    $villageLogo = $existingProfile['village_logo'] ?? null;

    // Periksa apakah admin mengunggah logo baru
    if (isset($_FILES['village_logo']) &&
        $_FILES['village_logo']['error'] !== UPLOAD_ERR_NO_FILE) {

        $file = $_FILES['village_logo'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload logo gagal.');
        }

        // Maksimal ukuran file 5 MB
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new RuntimeException('Ukuran logo maksimal 5 MB.');
        }

        // Periksa MIME type gambar
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        if (!isset($allowedTypes[$mimeType])) {
            throw new RuntimeException(
                'Format logo harus JPG, PNG, atau WEBP.'
            );
        }

        // Siapkan folder penyimpanan logo
        $uploadDirectory = __DIR__ . '/../uploads/village/';

        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0755, true);
        }

        if (!is_writable($uploadDirectory)) {
            throw new RuntimeException(
                'Folder upload logo tidak dapat ditulisi.'
            );
        }

        // Buat nama file acak agar tidak bentrok
        $fileName = bin2hex(random_bytes(16)) . '.' .
                    $allowedTypes[$mimeType];

        $destination = $uploadDirectory . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException(
                'Gagal menyimpan file logo.'
            );
        }

        // Simpan lokasi relatif logo
        $newFilePath = 'uploads/village/' . $fileName;
        $villageLogo = $newFilePath;
    }

    // Simpan data profil menggunakan transaksi
    $pdo->beginTransaction();

    if ($existingProfile) {

        // Perbarui profil yang sudah ada
        $sql = "UPDATE village_profile SET
                    village_name = :village_name,
                    district = :district,
                    regency = :regency,
                    province = :province,
                    description = :description,
                    history = :history,
                    vision = :vision,
                    mission = :mission,
                    geographic_info = :geographic_info,
                    village_address = :village_address,
                    village_logo = :village_logo
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':village_name' => $villageName,
            ':district' => $district,
            ':regency' => $regency,
            ':province' => $province,
            ':description' => $description,
            ':history' => $history,
            ':vision' => $vision,
            ':mission' => $mission,
            ':geographic_info' => $geographicInfo,
            ':village_address' => $villageAddress,
            ':village_logo' => $villageLogo,
            ':id' => $existingProfile['id']
        ]);

        $message = 'Profil desa berhasil diperbarui.';

    } else {

        // Tambahkan profil pertama
        $sql = "INSERT INTO village_profile (
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
                    village_logo
                ) VALUES (
                    :village_name,
                    :district,
                    :regency,
                    :province,
                    :description,
                    :history,
                    :vision,
                    :mission,
                    :geographic_info,
                    :village_address,
                    :village_logo
                )";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':village_name' => $villageName,
            ':district' => $district,
            ':regency' => $regency,
            ':province' => $province,
            ':description' => $description,
            ':history' => $history,
            ':vision' => $vision,
            ':mission' => $mission,
            ':geographic_info' => $geographicInfo,
            ':village_address' => $villageAddress,
            ':village_logo' => $villageLogo
        ]);

        $message = 'Profil desa berhasil disimpan.';
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => $message,
        'village_logo' => $villageLogo
    ]);

} catch (Throwable $e) {

    // Batalkan perubahan database jika terjadi kesalahan
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Hapus file baru jika gagal disimpan ke database
    if ($newFilePath !== null) {
        $fileToDelete = __DIR__ . '/../' . $newFilePath;

        if (is_file($fileToDelete)) {
            unlink($fileToDelete);
        }
    }

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => $e instanceof RuntimeException
            ? $e->getMessage()
            : 'Terjadi kesalahan saat menyimpan profil desa.'
    ]);
}
