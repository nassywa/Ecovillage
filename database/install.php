<?php
/**
 * Instalasi database lewat terminal (alternatif dari halaman /setup).
 *   php database/install.php [email_admin] [password_admin]
 * Koneksi database dibaca dari file .env atau Environment Variables.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('Jalankan dari terminal.');
}

require __DIR__ . '/../src/bootstrap.php';

$email = $argv[1] ?? 'admin@margamulya.id';
$pass = $argv[2] ?? 'admin12345';

App\Setup\install(App\db(), $email, $pass);
echo "Instalasi selesai. Admin: {$email}\n";
