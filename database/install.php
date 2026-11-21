<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('Jalankan dari terminal.');
}

require __DIR__ . '/../src/bootstrap.php';

$email = $argv[1] ?? 'admin@margamulya.id';
$pass = $argv[2] ?? 'admin12345';

App\Setup\install(App\db(), $email, $pass);
echo "Instalasi selesai. Admin: {$email}\n";
