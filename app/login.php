
<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

// Login hanya menerima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Metode request tidak diizinkan.'
    ]);

    exit;
}
// Ambil data login
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validasi input
if ($username === '' || $password === '') {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Username dan password wajib diisi.'
    ]);

    exit;
}
try {

    // Cari akun admin berdasarkan username
    $sql = "SELECT id, username, password_hash, name
            FROM admins
            WHERE username = :username
            LIMIT 1";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':username' => $username
    ]);

    $admin = $stmt->fetch();

    // Periksa password
    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        http_response_code(401);

        echo json_encode([
            'success' => false,
            'message' => 'Username atau password salah.'
        ]);

        exit;
    }

    // Buat session login
    session_regenerate_id(true);

    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_name'] = $admin['name'];

    echo json_encode([
        'success' => true,
        'message' => 'Login berhasil.',
        'admin' => [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'name' => $admin['name']
        ]
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan pada server.'
    ]);

}