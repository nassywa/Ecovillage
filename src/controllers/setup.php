<?php

declare(strict_types=1);

namespace App\Setup;

use Throwable;
use function App\{env, db, render, e};

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
        echo render('pages/setup', ['message' => 'Instalasi gagal: ' . $ex->getMessage(), 'ok' => false, 'enabled' => true]);
    }
}

function install(\PDO $pdo, string $adminEmail, string $adminPass): void
{
    $sql = file_get_contents(\App\ROOT . '/database/schema.sql');

    $sql = preg_replace('/^\s*--.*$/m', '', $sql);
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
        $pdo->exec($stmt);
    }
    require_once \App\ROOT . '/database/seed.php';
    \Database\seed($pdo, $adminEmail, $adminPass);
}
