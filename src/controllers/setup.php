<?php

declare(strict_types=1);

namespace App\Setup;

use Throwable;
use function App\{env, db, render};

function page(): void
{
    echo render('pages/setup', ['message' => null, 'ok' => false, 'enabled' => (bool) env('SETUP_KEY')]);
}

function run(): void
{
    $key = env('SETUP_KEY');
    if (!$key) {
        http_response_code(403);
        echo render('pages/setup', ['message' => 'SETUP_KEY belum diatur di Environment Variables.', 'ok' => false, 'enabled' => false]);
        return;
    }
    if (!hash_equals($key, (string) ($_POST['key'] ?? ''))) {
        http_response_code(403);
        echo render('pages/setup', ['message' => 'Kunci setup salah.', 'ok' => false, 'enabled' => true]);
        return;
    }
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $pass = (string) ($_POST['password'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8) {
        echo render('pages/setup', ['message' => 'Isi email admin yang valid dan kata sandi minimal 8 karakter.', 'ok' => false, 'enabled' => true]);
        return;
    }
    try {
        install(db(), $email, $pass);
        echo render('pages/setup', ['message' => 'Instalasi selesai. Database sudah berisi data contoh Desa Marga Mulya.', 'ok' => true, 'enabled' => true]);
    } catch (Throwable $ex) {
        error_log('[SETUP] ' . $ex->getMessage());
        http_response_code(500);
        echo render('pages/setup', ['message' => 'Instalasi gagal. Periksa log server dan pastikan target database memang kosong.', 'ok' => false, 'enabled' => true]);
    }
}

function install(\PDO $pdo, string $adminEmail, string $adminPass): void
{
    // schema.sql is a fresh-install script and intentionally contains DROP statements.
    // Refuse non-empty databases so a leaked/reused setup key cannot wipe data.
    $existing = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN (
        'media', 'settings', 'sessions', 'users', 'reports', 'report_updates',
        'admins', 'village_contact', 'village_gallery', 'village_information', 'village_profile'
    ) LIMIT 1")->fetchColumn();
    if ($existing !== false) {
        throw new \RuntimeException('Database tidak kosong (tabel ' . $existing . ' ditemukan). Gunakan migrasi terpisah; installer ditolak.');
    }

    $sql = file_get_contents(\App\ROOT . '/database/schema.sql');
    if ($sql === false) {
        throw new \RuntimeException('File schema database tidak ditemukan.');
    }

    $sql = preg_replace('/^\s*--.*$/m', '', $sql);
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
        $pdo->exec($stmt);
    }
    require_once \App\ROOT . '/database/seed.php';
    \Database\seed($pdo, $adminEmail, $adminPass);
}
