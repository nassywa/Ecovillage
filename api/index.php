<?php
/**
 * Front controller EcoVillage Desa Marga Mulya.
 * Di Vercel semua request non-statis diarahkan ke file ini (lihat vercel.json).
 * Lokal: php -S localhost:8000 api/index.php  (dijalankan dari folder root proyek)
 */

declare(strict_types=1);

$root = dirname(__DIR__);

// Saat dijalankan dengan PHP built-in server lokal, biarkan file statis dilayani langsung.
if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $file = realpath($root . $path);
    if (
        $file !== false && is_file($file) && str_starts_with(str_replace('\\', '/', $file), str_replace('\\', '/', $root) . '/assets/')
    ) {
        return false;
    }
}

require $root . '/src/bootstrap.php';

App\run();
