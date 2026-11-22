<?php

declare(strict_types=1);

define('ROOT', dirname(__DIR__));
require ROOT . '/src/core.php';

function assert_throws(callable $callback, string $expected): void
{
    try {
        $callback();
    } catch (Throwable $error) {
        if ($error->getMessage() !== $expected) {
            throw new RuntimeException('Pesan error tidak sesuai: ' . $error->getMessage());
        }
        return;
    }
    throw new RuntimeException('Ekspektasi exception tidak terpenuhi.');
}

putenv('DB_SSL=true');
unset($_ENV['DB_SSL_CA'], $_SERVER['DB_SSL_CA']);
putenv('DB_SSL_CA');
assert_throws(fn () => \App\db(), 'DB_SSL_CA wajib diatur saat DB_SSL aktif.');

putenv('DB_SSL_CA=not-a-certificate');
assert_throws(fn () => \App\db(), 'DB_SSL_CA tidak valid.');

$systemCa = 'C:\\xampp\\apache\\bin\\curl-ca-bundle.crt';
if (!is_readable($systemCa)) {
    throw new RuntimeException('CA bundle pengujian tidak tersedia.');
}
putenv('DB_SSL_CA=' . $systemCa);
$options = \App\db_ssl_options();
if (($options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] ?? false) !== true) {
    throw new RuntimeException('Verifikasi sertifikat tidak diwajibkan.');
}
if (!is_readable((string) ($options[PDO::MYSQL_ATTR_SSL_CA] ?? ''))) {
    throw new RuntimeException('File CA hasil normalisasi tidak dapat dibaca.');
}

echo "database_tls_test: PASS\n";
