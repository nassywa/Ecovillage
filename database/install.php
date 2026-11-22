<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('Jalankan dari terminal.');
}

require __DIR__ . '/../src/bootstrap.php';

if (!isset($argv[1], $argv[2]) || !filter_var($argv[1], FILTER_VALIDATE_EMAIL) || strlen($argv[2]) < 8) {
    fwrite(STDERR, "Usage: php database/install.php ADMIN_EMAIL ADMIN_PASSWORD\n");
    exit(2);
}

$email = strtolower($argv[1]);
$pass = $argv[2];

App\Setup\install(App\db(), $email, $pass);
echo "Instalasi selesai. Admin: {$email}\n";
