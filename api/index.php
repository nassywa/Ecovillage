<?php

declare(strict_types=1);

$root = dirname(__DIR__);

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
