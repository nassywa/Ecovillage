<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;
use SessionHandlerInterface;
use Throwable;

function env(string $key, ?string $default = null): ?string
{
    $v = getenv($key);
    if ($v === false || $v === '') {
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
    return $v;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $url = env('DATABASE_URL');
    if ($url) {
        $p = parse_url($url);
        $host = $p['host'] ?? 'localhost';
        $port = (string) ($p['port'] ?? 3306);
        $name = ltrim($p['path'] ?? '', '/');
        $user = urldecode($p['user'] ?? '');
        $pass = urldecode($p['pass'] ?? '');
    } else {
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');
        $name = env('DB_NAME', 'ecovillage');
        $user = env('DB_USER', 'root');
        $pass = env('DB_PASS', '');
    }

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 10,
    ];

    if (in_array(strtolower((string) env('DB_SSL', 'false')), ['1', 'true', 'yes', 'required'], true)) {
        $options += db_ssl_options();
    }

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException) {
        // Do not expose a DSN, username, host, or password through application errors.
        throw new \RuntimeException('Koneksi database gagal.');
    }
    $pdo->exec("SET time_zone = '+07:00'");
    return $pdo;
}

/** @return array<int, mixed> */
function db_ssl_options(): array
{
    $configured = env('DB_SSL_CA');
    if (!$configured) {
        throw new \RuntimeException('DB_SSL_CA wajib diatur saat DB_SSL aktif.');
    }

    $caFile = $configured;
    $caContents = is_file($configured) ? @file_get_contents($configured) : str_replace('\\n', "\n", $configured);
    if ($caContents === false || trim($caContents) === '') {
        throw new \RuntimeException('DB_SSL_CA tidak valid.');
    }

    $certificate = null;
    if (preg_match('/-----BEGIN CERTIFICATE-----.+?-----END CERTIFICATE-----/s', $caContents, $matches) === 1) {
        $certificate = $matches[0];
    }
    if ($certificate === null || openssl_x509_read($certificate) === false) {
        throw new \RuntimeException('DB_SSL_CA tidak valid.');
    }

    if (!is_file($configured)) {
        $caFile = sys_get_temp_dir() . '/ecovillage-db-ca-' . hash('sha256', $caContents) . '.pem';
        if (!is_file($caFile) && @file_put_contents($caFile, $caContents, LOCK_EX) === false) {
            throw new \RuntimeException('DB_SSL_CA tidak dapat disiapkan.');
        }
        @chmod($caFile, 0600);
    }
    if (!is_readable($caFile)) {
        throw new \RuntimeException('DB_SSL_CA tidak dapat dibaca.');
    }

    return [
        PDO::MYSQL_ATTR_SSL_CA => $caFile,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
    ];
}

function q(string $sql, array $params = []): \PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function one(string $sql, array $params = []): ?array
{
    $row = q($sql, $params)->fetch();
    return $row === false ? null : $row;
}

function all(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}

function val(string $sql, array $params = []): mixed
{
    $v = q($sql, $params)->fetchColumn();
    return $v === false ? null : $v;
}

function insert(string $table, array $data): int
{
    $cols = array_keys($data);
    $sql = sprintf(
        'INSERT INTO `%s` (%s) VALUES (%s)',
        $table,
        implode(',', array_map(fn ($c) => "`$c`", $cols)),
        implode(',', array_map(fn ($c) => ":$c", $cols))
    );
    q($sql, array_combine(array_map(fn ($c) => ":$c", $cols), array_values($data)));
    return (int) db()->lastInsertId();
}

function update(string $table, array $data, int $id): void
{
    if (!$data) {
        return;
    }
    $set = implode(',', array_map(fn ($c) => "`$c` = :$c", array_keys($data)));
    $params = array_combine(array_map(fn ($c) => ":$c", array_keys($data)), array_values($data));
    $params[':__id'] = $id;
    q("UPDATE `$table` SET $set WHERE id = :__id", $params);
}

function setting(string $key, string $default = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            foreach (all('SELECT skey, svalue FROM settings') as $r) {
                $cache[$r['skey']] = (string) $r['svalue'];
            }
        } catch (Throwable) {
            $cache = [];
        }
    }
    return $cache[$key] ?? $default;
}

function save_setting(string $key, string $value): void
{
    q(
        'INSERT INTO settings (skey, svalue) VALUES (:k, :v) ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)',
        [':k' => $key, ':v' => $value]
    );
}

final class DbSessionHandler implements SessionHandlerInterface
{
    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $row = one('SELECT data FROM sessions WHERE id = :id AND expires_at > :now', [':id' => $id, ':now' => time()]);
        return $row['data'] ?? '';
    }

    public function write(string $id, string $data): bool
    {
        if ($data === '') {
            q('DELETE FROM sessions WHERE id = :id', [':id' => $id]);
            return true;
        }
        q(
            'INSERT INTO sessions (id, data, expires_at) VALUES (:id, :d, :e)
             ON DUPLICATE KEY UPDATE data = VALUES(data), expires_at = VALUES(expires_at)',
            [':id' => $id, ':d' => $data, ':e' => time() + 60 * 60 * 24 * 7]
        );
        return true;
    }

    public function destroy(string $id): bool
    {
        q('DELETE FROM sessions WHERE id = :id', [':id' => $id]);
        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        return q('DELETE FROM sessions WHERE expires_at < :now', [':now' => time()])->rowCount();
    }
}

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_set_save_handler(new DbSessionHandler(), true);
    session_name('ecovillage_sid');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.gc_probability', '1');
    ini_set('session.gc_divisor', '100');
    session_start();
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function user(): ?array
{
    static $u = false;
    if ($u !== false) {
        return $u;
    }
    $id = $_SESSION['user_id'] ?? null;
    $u = $id ? one('SELECT id, name, email, phone, role FROM users WHERE id = :id', [':id' => $id]) : null;
    return $u;
}

function is_admin(): bool
{
    return (user()['role'] ?? '') === 'admin';
}

function login_user(array $u): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $u['id'];
}

function require_login(): array
{
    $u = user();
    if (!$u) {
        flash('info', 'Silakan masuk terlebih dahulu.');
        redirect('/masuk?kembali=' . rawurlencode($_SERVER['REQUEST_URI'] ?? '/'));
    }
    return $u;
}

function require_admin(): array
{
    $u = user();
    if (!$u) {
        redirect('/masuk?peran=admin&kembali=' . rawurlencode($_SERVER['REQUEST_URI'] ?? '/admin'));
    }
    if ($u['role'] !== 'admin') {
        abort(403, 'Halaman ini khusus admin desa.');
    }
    return $u;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        abort(419, 'Sesi formulir sudah kedaluwarsa. Silakan muat ulang halaman lalu coba lagi.');
    }
}

function flash(string $type, string $msg): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'msg' => $msg];
}

function take_flash(): array
{
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

function old(string $key, string $default = ''): string
{
    return (string) ($_SESSION['_old'][$key] ?? $default);
}

function keep_old(array $data): void
{
    $_SESSION['_old'] = array_map(fn ($v) => is_string($v) ? $v : '', $data);
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

function input(string $key, string $default = ''): string
{
    $v = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($v) ? trim($v) : $default;
}

function e(mixed $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $to): never
{
    header('Location: ' . $to, true, 303);
    exit;
}

function back(string $fallback = '/'): never
{
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($ref && parse_url($ref, PHP_URL_HOST) === $host) {
        redirect($ref);
    }
    redirect($fallback);
}

function json_out(mixed $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function abort(int $code, string $message = ''): never
{
    http_response_code($code);
    $titles = [403 => 'Akses ditolak', 404 => 'Halaman tidak ditemukan', 419 => 'Sesi kedaluwarsa', 500 => 'Terjadi kesalahan'];
    view('pages/error', [
        'title' => $titles[$code] ?? 'Terjadi kesalahan',
        'code' => $code,
        'message' => $message ?: ($code === 404 ? 'Halaman yang Anda cari tidak tersedia atau sudah dipindahkan.' : 'Silakan coba beberapa saat lagi.'),
    ]);
    exit;
}

function view(string $name, array $data = [], ?string $layout = 'public'): void
{
    $content = render($name, $data);
    if ($layout === null) {
        echo $content;
        return;
    }
    echo render('layouts/' . $layout, $data + ['content' => $content]);
}

function render(string $name, array $data = []): string
{
    extract($data, EXTR_SKIP);
    ob_start();
    require ROOT . '/src/views/' . $name . '.php';
    return (string) ob_get_clean();
}

function current_path(): string
{
    $p = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    return rtrim($p, '/') ?: '/';
}

function active(string $prefix): string
{
    $p = current_path();
    $on = $prefix === '/' ? $p === '/' : ($p === $prefix || str_starts_with($p, $prefix . '/'));
    return $on ? ' aria-current="page"' : '';
}

const MAX_UPLOAD = 3_000_000;
const ALLOWED_MIME = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

function store_upload(string $field): ?int
{
    $f = $_FILES[$field] ?? null;
    if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE) {
        throw new \RuntimeException('Ukuran foto terlalu besar. Maksimal 3 MB.');
    }
    if ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
        throw new \RuntimeException('Foto gagal diunggah. Silakan coba lagi.');
    }
    if ($f['size'] <= 0 || $f['size'] > MAX_UPLOAD) {
        throw new \RuntimeException('Ukuran foto terlalu besar. Maksimal 3 MB.');
    }
    $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if (!isset(ALLOWED_MIME[$mime])) {
        throw new \RuntimeException('Format foto harus JPG, PNG, atau WEBP.');
    }
    $data = file_get_contents($f['tmp_name']);
    return store_media($data, $mime, (string) $f['name']);
}

function store_media(string $data, string $mime, string $name = ''): int
{
    $stmt = db()->prepare('INSERT INTO media (mime, size, original_name, data) VALUES (:m, :s, :n, :d)');
    $stmt->bindValue(':m', $mime);
    $stmt->bindValue(':s', strlen($data), PDO::PARAM_INT);
    $stmt->bindValue(':n', mb_substr($name, 0, 190));
    $stmt->bindValue(':d', $data, PDO::PARAM_LOB);
    $stmt->execute();
    return (int) db()->lastInsertId();
}

function delete_media(?int $id): void
{
    if ($id) {
        q('DELETE FROM media WHERE id = :id', [':id' => $id]);
    }
}

function media_url(mixed $id, string $fallback = '/assets/img/placeholder.svg'): string
{
    return $id ? '/media/' . (int) $id : $fallback;
}

const BULAN = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const BULAN_PENDEK = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

function tgl(?string $date, bool $short = false, bool $time = false): string
{
    if (!$date) {
        return '-';
    }
    $t = strtotime($date);
    if ($t === false) {
        return '-';
    }
    $s = (int) date('j', $t) . ' ' . ($short ? BULAN_PENDEK : BULAN)[(int) date('n', $t)] . ' ' . date('Y', $t);
    if ($time) {
        $s .= ', ' . date('H.i', $t) . ' WIB';
    }
    return $s;
}

function angka(mixed $n, int $dec = 0): string
{
    if ($n === null || $n === '') {
        return '-';
    }
    $f = (float) $n;
    if ($dec === 0 && floor($f) != $f) {
        $dec = 2;
    }
    $s = number_format($f, $dec, ',', '.');
    return $dec > 0 ? rtrim(rtrim($s, '0'), ',') : $s;
}

function slugify(string $text): string
{
    $text = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $text) ?: $text), '-'));
    return $text !== '' ? mb_substr($text, 0, 120) : 'item-' . bin2hex(random_bytes(3));
}

function excerpt(?string $text, int $len = 140): string
{
    $t = trim(preg_replace('/\s+/', ' ', strip_tags((string) $text)));
    return mb_strlen($t) > $len ? rtrim(mb_substr($t, 0, $len)) . '…' : $t;
}

function paragraphs(?string $text): string
{
    $out = '';
    foreach (preg_split('/\R{2,}/', trim((string) $text)) as $p) {
        if (trim($p) !== '') {
            $out .= '<p>' . nl2br(e(trim($p))) . '</p>';
        }
    }
    return $out;
}

function paginate(int $total, int $perPage, int $page): array
{
    $pages = max(1, (int) ceil($total / $perPage));
    $page = max(1, min($page, $pages));
    return ['total' => $total, 'per' => $perPage, 'page' => $page, 'pages' => $pages, 'offset' => ($page - 1) * $perPage];
}

function qs(array $changes): string
{
    $params = array_merge($_GET, $changes);
    $params = array_filter($params, fn ($v) => $v !== null && $v !== '');
    $path = current_path();
    return $path . ($params ? '?' . http_build_query($params) : '');
}

function report_status_class(string $status): string
{
    return match ($status) {
        'Menunggu' => 'badge--wait',
        'Diterima' => 'badge--info',
        'Diproses' => 'badge--warn',
        'Selesai' => 'badge--ok',
        'Ditolak' => 'badge--danger',
        default => '',
    };
}

const REPORT_STATUSES = ['Menunggu', 'Diterima', 'Diproses', 'Selesai', 'Ditolak'];

function office_hours(): array
{
    static $rows = null;
    return $rows ??= all('SELECT * FROM office_hours ORDER BY sort, id');
}

function social_links(): array
{
    return array_filter([
        'facebook' => ['Facebook', setting('social_facebook')],
        'tiktok' => ['TikTok', setting('social_tiktok')],
        'youtube' => ['YouTube', setting('social_youtube')],
        'instagram' => ['Instagram', setting('social_instagram')],
    ], fn ($s) => $s[1] !== '');
}

function honeypot_tripped(): bool
{
    return !empty($_POST['website']);
}
