<?php

declare(strict_types=1);

namespace App;

const ROOT = __DIR__ . '/..';

date_default_timezone_set('Asia/Jakarta');
mb_internal_encoding('UTF-8');

(function (): void {
    $envFile = ROOT . '/.env';
    if (!is_file($envFile)) {
        return;
    }
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        $v = trim($v, "\"'");
        if (getenv($k) === false) {
            putenv("$k=$v");
            $_ENV[$k] = $v;
        }
    }
})();

require __DIR__ . '/core.php';
require __DIR__ . '/icons.php';
require __DIR__ . '/crud.php';
require __DIR__ . '/controllers/public.php';
require __DIR__ . '/controllers/admin.php';
require __DIR__ . '/controllers/setup.php';
require __DIR__ . '/routes.php';
