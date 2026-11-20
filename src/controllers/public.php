<?php

declare(strict_types=1);

namespace App\Pub;

use function App\{all, one, val, q, insert, setting, view, redirect, back, flash, input, keep_old, clear_old,
    user, login_user, abort, json_out, store_upload, paginate, honeypot_tripped, excerpt, tgl, media_url,
    office_hours, env, e};

/* ---------------------------------------------------------
 * BERANDA
 * ------------------------------------------------------- */
function home(): void
{
    view('pages/home', [
        'title' => 'Beranda',
        'news' => all("SELECT id, title, category, published_at, image_id FROM news WHERE status = 'Terbit' ORDER BY published_at DESC, id DESC LIMIT 4"),
        'featured' => all("SELECT id, name, category, subcategory, description, price, image_id FROM places WHERE status = 'Tampil' AND featured = 1 ORDER BY id LIMIT 3"),
        'hours' => office_hours(),
        'reportStats' => one("SELECT COUNT(*) total, SUM(status = 'Selesai') selesai FROM reports"),
    ]);
}

/* ---------------------------------------------------------
 * PROFIL DESA
 * ------------------------------------------------------- */
function profile(): void
{
    $landGroup = one("SELECT id, title, unit FROM stat_groups WHERE slug = 'penggunaan-lahan'");
    $land = $landGroup ? all('SELECT label, value FROM stat_items WHERE group_id = :g ORDER BY sort, id', [':g' => $landGroup['id']]) : [];

    view('pages/profile', [
        'title' => 'Profil Desa',
        'officials' => all('SELECT * FROM officials ORDER BY tier, sort, id'),
        'land' => $land,
        'landUnit' => $landGroup['unit'] ?? 'ha',
        'missions' => array_values(array_filter(array_map('trim', explode("\n", setting('profil_misi'))))),
        'regionCount' => all('SELECT level, COUNT(*) n FROM map_regions GROUP BY level'),
        'pageScripts' => ['leaflet', 'chart'],
    ]);
}

/* ---------------------------------------------------------
 * INFORMASI DESA (A–H dalam grafik)
 * ------------------------------------------------------- */
function information(): void
{
    $sections = all('SELECT * FROM stat_sections ORDER BY sort, code');
    $groups = all('SELECT * FROM stat_groups ORDER BY sort, id');
    $items = all('SELECT * FROM stat_items ORDER BY sort, id');

    $itemsByGroup = [];
    foreach ($items as $it) {
        $itemsByGroup[$it['group_id']][] = $it;
    }

    $bySection = [];
    foreach ($groups as $g) {
        $g['chart'] = chart_payload($g, $itemsByGroup[$g['id']] ?? []);
        $bySection[$g['section_id']][] = $g;
    }

    $active = strtoupper(input('bagian', $sections[0]['code'] ?? 'A'));

    view('pages/information', [
        'title' => 'Informasi Desa',
        'sections' => $sections,
        'bySection' => $bySection,
        'active' => $active,
        'pageScripts' => ['chart'],
    ]);
}

/** Ubah baris stat_items menjadi struktur label × seri untuk Chart.js */
function chart_payload(array $group, array $items): array
{
    $labels = [];
    $series = [];
    $matrix = [];
    foreach ($items as $it) {
        $label = $it['label'];
        $s = $it['series'] !== null && $it['series'] !== '' ? $it['series'] : 'Jumlah';
        if (!in_array($label, $labels, true)) {
            $labels[] = $label;
        }
        if (!in_array($s, $series, true)) {
            $series[] = $s;
        }
        $matrix[$s][$label] = (float) $it['value'];
    }
    $datasets = [];
    foreach ($series as $s) {
        $datasets[] = ['label' => $s, 'data' => array_map(fn ($l) => $matrix[$s][$l] ?? 0, $labels)];
    }
    return [
        'type' => $group['chart_type'],
        'unit' => (string) $group['unit'],
        'labels' => $labels,
        'datasets' => $datasets,
    ];
}

/* ---------------------------------------------------------
 * GALERI
 * ------------------------------------------------------- */
function gallery(): void
{
    $cat = input('kategori');
    $where = "status = 'Disetujui'";
    $params = [];
    if ($cat !== '') {
        $where .= ' AND category = :c';
        $params[':c'] = $cat;
    }
    $total = (int) val("SELECT COUNT(*) FROM gallery WHERE $where", $params);
    $pg = paginate($total, 12, (int) input('hal', '1'));
    $items = all("SELECT g.*, u.name AS user_name FROM gallery g LEFT JOIN users u ON u.id = g.user_id
                  WHERE $where ORDER BY g.sort DESC, g.created_at DESC, g.id DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}", $params);
    $cats = all("SELECT category, COUNT(*) n FROM gallery WHERE status = 'Disetujui' AND category IS NOT NULL AND category <> '' GROUP BY category ORDER BY category");

    $mine = [];
    if ($u = user()) {
        $mine = all('SELECT id, title, status, created_at FROM gallery WHERE user_id = :u ORDER BY id DESC LIMIT 5', [':u' => $u['id']]);
    }

    view('pages/gallery', [
        'title' => 'Galeri',
        'items' => $items,
        'pg' => $pg,
        'cats' => $cats,
        'cat' => $cat,
        'mine' => $mine,
    ]);
}

function gallery_submit(): void
{
    $u = user();
    if (!$u) {
        flash('info', 'Masuk atau daftar akun warga dulu untuk mengirim foto.');
        redirect('/masuk?kembali=/galeri');
    }
    $title = input('title');
    $category = input('category');
    $desc = input('description');
    if ($title === '' || mb_strlen($title) > 150) {
        flash('error', 'Judul foto wajib diisi (maksimal 150 karakter).');
        redirect('/galeri#kirim-foto');
    }
    try {
        $mediaId = store_upload('image');
    } catch (\RuntimeException $ex) {
        flash('error', $ex->getMessage());
        redirect('/galeri#kirim-foto');
    }
    if (!$mediaId) {
        flash('error', 'Silakan pilih foto yang akan dikirim.');
        redirect('/galeri#kirim-foto');
    }
    $isAdmin = $u['role'] === 'admin';
    insert('gallery', [
        'title' => $title,
        'category' => $category !== '' ? mb_substr($category, 0, 60) : null,
        'description' => $desc !== '' ? mb_substr($desc, 0, 300) : null,
        'image_id' => $mediaId,
        'status' => $isAdmin ? 'Disetujui' : 'Menunggu',
        'user_id' => $u['id'],
        'uploader_name' => $u['name'],
    ]);
    flash('success', $isAdmin
        ? 'Foto berhasil ditambahkan ke galeri.'
        : 'Terima kasih! Foto Anda sudah terkirim dan akan tampil setelah disetujui admin desa.');
    redirect('/galeri');
}

/* ---------------------------------------------------------
 * JELAJAH DESA
 * ------------------------------------------------------- */
function explore(): void
{
    $cat = input('kategori');
    $search = input('cari');
    $where = "status = 'Tampil'";
    $params = [];
    if (in_array($cat, ['UMKM', 'Wisata', 'Budaya'], true)) {
        $where .= ' AND category = :c';
        $params[':c'] = $cat;
    } else {
        $cat = '';
    }
    if ($search !== '') {
        $where .= ' AND (name LIKE :q1 OR description LIKE :q2 OR subcategory LIKE :q3 OR price LIKE :q4)';
        $like = '%' . mb_substr($search, 0, 60) . '%';
        $params += [':q1' => $like, ':q2' => $like, ':q3' => $like, ':q4' => $like];
    }
    $total = (int) val("SELECT COUNT(*) FROM places WHERE $where", $params);
    $pg = paginate($total, 6, (int) input('hal', '1'));
    $items = all("SELECT * FROM places WHERE $where ORDER BY featured DESC, name LIMIT {$pg['per']} OFFSET {$pg['offset']}", $params);
    $counts = [];
    foreach (all("SELECT category, COUNT(*) n FROM places WHERE status = 'Tampil' GROUP BY category") as $r) {
        $counts[$r['category']] = (int) $r['n'];
    }

    view('pages/explore', [
        'title' => 'Sudut Istimewa',
        'spots' => all("SELECT * FROM places WHERE status = 'Tampil' AND featured = 1 AND category <> 'UMKM' ORDER BY id LIMIT 4"),
        'products' => all("SELECT * FROM places WHERE status = 'Tampil' AND featured = 1 AND category = 'UMKM' ORDER BY id LIMIT 3"),
        'items' => $items,
        'pg' => $pg,
        'cat' => $cat,
        'search' => $search,
        'counts' => $counts,
    ]);
}

function api_place(string $id): void
{
    $p = one("SELECT * FROM places WHERE id = :id AND status = 'Tampil'", [':id' => $id]);
    if (!$p) {
        json_out(['error' => 'Tidak ditemukan'], 404);
    }
    $p['image'] = media_url($p['image_id']);
    json_out($p);
}

/* ---------------------------------------------------------
 * BERITA & KEGIATAN
 * ------------------------------------------------------- */
function news(): void
{
    $cat = input('kategori');
    $search = input('cari');
    $where = "status = 'Terbit'";
    $params = [];
    if (in_array($cat, ['Berita', 'Kegiatan', 'Pengumuman'], true)) {
        $where .= ' AND category = :c';
        $params[':c'] = $cat;
    } else {
        $cat = '';
    }
    if ($search !== '') {
        $where .= ' AND (title LIKE :q1 OR content LIKE :q2)';
        $like = '%' . mb_substr($search, 0, 60) . '%';
        $params += [':q1' => $like, ':q2' => $like];
    }
    // Berita utama: ditandai admin, atau berita terbaru bila tidak ada yang ditandai
    $featured = $search === '' ? one("SELECT * FROM news WHERE $where ORDER BY featured DESC, published_at DESC, id DESC LIMIT 1", $params) : null;
    $listWhere = $where;
    $listParams = $params;
    if ($featured) {
        $listWhere .= ' AND id <> :fid';
        $listParams[':fid'] = $featured['id'];
    }
    $total = (int) val("SELECT COUNT(*) FROM news WHERE $listWhere", $listParams);
    $pg = paginate($total, 6, (int) input('hal', '1'));
    $items = all("SELECT id, title, category, excerpt, content, image_id, author, published_at FROM news
                  WHERE $listWhere ORDER BY published_at DESC, id DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}", $listParams);

    view('pages/news', [
        'title' => 'Berita & Kegiatan',
        'items' => $items,
        'featured' => $featured,
        'pg' => $pg,
        'cat' => $cat,
        'search' => $search,
        'article' => ($open = (int) input('baca', '0'))
            ? one("SELECT * FROM news WHERE id = :id AND status = 'Terbit'", [':id' => $open]) : null,
    ]);
}

function api_news(string $id): void
{
    $n = one("SELECT id, title, category, content, image_id, author, published_at FROM news WHERE id = :id AND status = 'Terbit'", [':id' => $id]);
    if (!$n) {
        json_out(['error' => 'Tidak ditemukan'], 404);
    }
    json_out([
        'id' => (int) $n['id'],
        'title' => $n['title'],
        'category' => $n['category'],
        'author' => $n['author'],
        'date' => tgl($n['published_at']),
        'image' => media_url($n['image_id']),
        'html' => \App\paragraphs($n['content']),
    ]);
}

/* ---------------------------------------------------------
 * LAPOR DESA
 * ------------------------------------------------------- */
function report_form(): void
{
    view('pages/report', [
        'title' => 'Lapor Desa',
        'pageScripts' => ['leaflet'],
        'categories' => all('SELECT * FROM report_categories ORDER BY sort, id'),
        'rts' => all("SELECT name, parent_name FROM map_regions WHERE level = 'rt' ORDER BY sort, id"),
        'done' => $_SESSION['_report_done'] ?? null,
    ]);
    unset($_SESSION['_report_done']);
}

function report_submit(): void
{
    if (honeypot_tripped()) {
        redirect('/lapor');
    }
    $u = user();
    if (!$u) {
        flash('info', 'Silakan masuk atau daftar akun terlebih dahulu untuk mengirim laporan.');
        redirect('/masuk?kembali=/lapor');
    }
    $data = [
        'category' => input('category'),
        'location' => input('location'),
        'rt' => input('rt'),
        'description' => input('description'),
        'reporter_name' => input('reporter_name', $u['name'] ?? ''),
        'reporter_phone' => input('reporter_phone', $u['phone'] ?? ''),
        'lat' => input('lat'),
        'lng' => input('lng'),
    ];
    keep_old($data);
    $lat = is_numeric($data['lat']) && abs((float) $data['lat']) <= 90 ? (float) $data['lat'] : null;
    $lng = is_numeric($data['lng']) && abs((float) $data['lng']) <= 180 ? (float) $data['lng'] : null;

    $validCats = array_column(all('SELECT name FROM report_categories'), 'name');
    $errors = [];
    if (!in_array($data['category'], $validCats, true)) {
        $errors[] = 'Pilih kategori masalah.';
    }
    if (mb_strlen($data['location']) < 3) {
        $errors[] = 'Lokasi kejadian wajib diisi.';
    }
    if (mb_strlen($data['description']) < 20) {
        $errors[] = 'Deskripsi masalah minimal 20 karakter agar petugas mudah memahami.';
    }
    if ($data['reporter_phone'] !== '' && !preg_match('/^[0-9+\-\s]{8,20}$/', $data['reporter_phone'])) {
        $errors[] = 'Nomor HP tidak valid.';
    }
    $photoId = null;
    if (!$errors) {
        try {
            $photoId = store_upload('photo');
        } catch (\RuntimeException $ex) {
            $errors[] = $ex->getMessage();
        }
    }
    if ($errors) {
        flash('error', implode(' ', $errors));
        redirect('/lapor#form-lapor');
    }

    $location = $data['location'] . ($data['rt'] !== '' ? ' (' . $data['rt'] . ')' : '');
    do {
        $ticket = 'LD-' . date('ym') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
    } while (val('SELECT 1 FROM reports WHERE ticket = :t', [':t' => $ticket]));

    $id = insert('reports', [
        'ticket' => $ticket,
        'user_id' => $u['id'] ?? null,
        'reporter_name' => $data['reporter_name'] !== '' ? mb_substr($data['reporter_name'], 0, 100) : null,
        'reporter_phone' => $data['reporter_phone'] !== '' ? $data['reporter_phone'] : null,
        'category' => $data['category'],
        'location' => mb_substr($location, 0, 200),
        'lat' => $lat,
        'lng' => $lng,
        'description' => mb_substr($data['description'], 0, 1500),
        'photo_id' => $photoId,
        'status' => 'Menunggu',
    ]);
    insert('report_updates', ['report_id' => $id, 'status' => 'Menunggu', 'note' => 'Laporan berhasil dikirim dan menunggu verifikasi petugas desa.']);
    clear_old();
    $_SESSION['_report_done'] = $ticket;
    redirect('/lapor#berhasil');
}

/* ---------------------------------------------------------
 * CEK STATUS LAPORAN
 * ------------------------------------------------------- */
function report_status(): void
{
    $ticket = strtoupper(trim(input('tiket')));
    $cat = input('kategori');
    $status = input('status');

    $detail = null;
    $history = [];
    if ($ticket !== '') {
        $detail = one('SELECT * FROM reports WHERE ticket = :t', [':t' => $ticket]);
        if ($detail) {
            $history = all('SELECT * FROM report_updates WHERE report_id = :r ORDER BY created_at, id', [':r' => $detail['id']]);
        }
    }

    $where = '1=1';
    $params = [];
    if ($cat !== '') {
        $where .= ' AND category = :c';
        $params[':c'] = $cat;
    }
    if (in_array($status, \App\REPORT_STATUSES, true)) {
        $where .= ' AND status = :s';
        $params[':s'] = $status;
    } else {
        $status = '';
    }
    if ($ticket !== '') {
        $where .= ' AND ticket LIKE :t';
        $params[':t'] = '%' . $ticket . '%';
    }
    $total = (int) val("SELECT COUNT(*) FROM reports WHERE $where", $params);
    $pg = paginate($total, 8, (int) input('hal', '1'));
    $rows = all("SELECT ticket, category, location, status, created_at, accepted_at FROM reports
                 WHERE $where ORDER BY created_at DESC, id DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}", $params);

    $summary = [];
    foreach (all('SELECT status, COUNT(*) n FROM reports GROUP BY status') as $r) {
        $summary[$r['status']] = (int) $r['n'];
    }

    view('pages/status', [
        'title' => 'Cek Status Laporan',
        'ticket' => $ticket,
        'detail' => $detail,
        'history' => $history,
        'rows' => $rows,
        'pg' => $pg,
        'cat' => $cat,
        'status' => $status,
        'categories' => all('SELECT name, icon FROM report_categories ORDER BY sort, id'),
        'summary' => $summary,
        'mine' => ($u = user()) ? all('SELECT ticket, category, status, created_at FROM reports WHERE user_id = :u ORDER BY id DESC LIMIT 5', [':u' => $u['id']]) : [],
    ]);
}

/* ---------------------------------------------------------
 * KONTAK
 * ------------------------------------------------------- */
function contact(): void
{
    view('pages/contact', [
        'title' => 'Kontak',
        'hours' => office_hours(),
        'pageScripts' => ['leaflet'],
    ]);
}

function contact_submit(): void
{
    if (honeypot_tripped()) {
        redirect('/kontak');
    }
    $data = [
        'name' => input('name'),
        'email' => input('email'),
        'phone' => input('phone'),
        'subject' => input('subject', 'Pesan dari website'),
        'message' => input('message'),
    ];
    keep_old($data);
    $errors = [];
    if (mb_strlen($data['name']) < 2) {
        $errors[] = 'Nama wajib diisi.';
    }
    if ($data['email'] === '' && $data['phone'] === '') {
        $errors[] = 'Isi email (atau nomor HP) agar kami bisa membalas.';
    }
    if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if ($data['subject'] === '') {
        $data['subject'] = 'Pesan dari website';
    }
    if (mb_strlen($data['message']) < 10) {
        $errors[] = 'Pesan minimal 10 karakter.';
    }
    if ($errors) {
        flash('error', implode(' ', $errors));
        redirect('/kontak#form-pesan');
    }
    insert('messages', [
        'name' => mb_substr($data['name'], 0, 100),
        'email' => $data['email'] ?: null,
        'phone' => $data['phone'] ?: null,
        'subject' => mb_substr($data['subject'], 0, 150),
        'message' => mb_substr($data['message'], 0, 3000),
    ]);
    clear_old();
    flash('success', 'Pesan Anda sudah terkirim. Petugas desa akan membalas pada jam pelayanan.');
    redirect('/kontak#form-pesan');
}

/* ---------------------------------------------------------
 * MASUK / DAFTAR
 * ------------------------------------------------------- */
function login_form(): void
{
    if ($u = user()) {
        redirect($u['role'] === 'admin' ? '/admin' : '/');
    }
    view('pages/login', [
        'title' => 'Masuk / Daftar',
        'noCta' => true,
        'tab' => input('tab', 'masuk') === 'daftar' ? 'daftar' : 'masuk',
        'role' => input('peran', 'warga') === 'admin' ? 'admin' : 'warga',
        'back' => safe_back(input('kembali')),
    ]);
}

function safe_back(string $url): string
{
    return ($url !== '' && str_starts_with($url, '/') && !str_starts_with($url, '//')) ? $url : '';
}

function login_submit(): void
{
    $email = strtolower(input('email'));
    $role = input('role') === 'admin' ? 'admin' : 'warga';
    $backTo = safe_back(input('kembali'));
    keep_old(['email' => $email]);

    $u = one('SELECT * FROM users WHERE email = :e', [':e' => $email]);
    if (!$u || !password_verify((string) ($_POST['password'] ?? ''), $u['password_hash'])) {
        usleep(300000);
        flash('error', 'Email atau kata sandi salah.');
        redirect('/masuk?peran=' . $role . ($backTo ? '&kembali=' . rawurlencode($backTo) : ''));
    }
    if ($role === 'admin' && $u['role'] !== 'admin') {
        flash('error', 'Akun ini terdaftar sebagai warga, bukan admin. Pilih "Warga" untuk masuk.');
        redirect('/masuk?peran=admin');
    }
    clear_old();
    login_user($u);
    flash('success', 'Selamat datang, ' . $u['name'] . '!');
    if ($backTo) {
        redirect($backTo);
    }
    redirect($u['role'] === 'admin' ? '/admin' : '/');
}

function register_submit(): void
{
    $data = [
        'name' => input('name'),
        'email' => strtolower(input('email')),
        'phone' => input('phone'),
        'role' => input('role') === 'admin' ? 'admin' : 'warga',
    ];
    keep_old($data);
    $pass = (string) ($_POST['password'] ?? '');
    $pass2 = (string) ($_POST['password_confirm'] ?? '');
    $errors = [];
    if (mb_strlen($data['name']) < 3) {
        $errors[] = 'Nama lengkap minimal 3 karakter.';
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    } elseif (val('SELECT 1 FROM users WHERE email = :e', [':e' => $data['email']])) {
        $errors[] = 'Email sudah terdaftar. Silakan masuk.';
    }
    if ($data['phone'] !== '' && !preg_match('/^[0-9+\-\s]{8,20}$/', $data['phone'])) {
        $errors[] = 'Nomor HP tidak valid.';
    }
    if (strlen($pass) < 8) {
        $errors[] = 'Kata sandi minimal 8 karakter.';
    } elseif ($pass !== $pass2) {
        $errors[] = 'Konfirmasi kata sandi tidak sama.';
    }
    if ($data['role'] === 'admin') {
        $code = setting('admin_register_code', env('ADMIN_REGISTER_CODE', ''));
        if ($code === '' || !hash_equals($code, input('admin_code'))) {
            $errors[] = 'Kode pendaftaran admin salah. Minta kode ke pengelola website desa.';
        }
    }
    if ($errors) {
        flash('error', implode(' ', $errors));
        redirect('/masuk?tab=daftar&peran=' . $data['role']);
    }
    $id = insert('users', [
        'name' => mb_substr($data['name'], 0, 100),
        'email' => $data['email'],
        'phone' => $data['phone'] ?: null,
        'password_hash' => password_hash($pass, PASSWORD_DEFAULT),
        'role' => $data['role'],
    ]);
    clear_old();
    login_user(['id' => $id]);
    flash('success', 'Akun berhasil dibuat. Selamat bergabung, ' . $data['name'] . '!');
    redirect($data['role'] === 'admin' ? '/admin' : '/');
}

function logout(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
    flash('success', 'Anda sudah keluar.');
    redirect('/');
}

/* ---------------------------------------------------------
 * DATA PETA (JSON)
 * ------------------------------------------------------- */
function api_map(): void
{
    $regions = all('SELECT id, level, name, parent_name, color, leader, households, population, geojson FROM map_regions ORDER BY sort, id');
    foreach ($regions as &$r) {
        $r['coords'] = json_decode($r['geojson'], true) ?: [];
        unset($r['geojson']);
    }
    unset($r);
    header('Cache-Control: public, max-age=60, s-maxage=300');
    json_out([
        'center' => [(float) setting('map_lat', '-2.47'), (float) setting('map_lng', '111.92')],
        'zoom' => (int) setting('map_zoom', '14'),
        'regions' => $regions,
        'points' => all('SELECT name, type, lat, lng, description FROM map_points ORDER BY sort, id'),
    ]);
}

/* ---------------------------------------------------------
 * GAMBAR
 * ------------------------------------------------------- */
function media(string $id): void
{
    $m = one('SELECT id, mime, size, data, created_at FROM media WHERE id = :id', [':id' => $id]);
    if (!$m) {
        http_response_code(404);
        header('Content-Type: image/svg+xml');
        readfile(\App\ROOT . '/assets/img/placeholder.svg');
        exit;
    }
    $etag = '"m' . $m['id'] . '-' . $m['size'] . '"';
    header('ETag: ' . $etag);
    header('Cache-Control: public, max-age=31536000, s-maxage=31536000, immutable');
    if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
        http_response_code(304);
        exit;
    }
    header('Content-Type: ' . $m['mime']);
    header('Content-Length: ' . strlen($m['data']));
    header('X-Content-Type-Options: nosniff');
    if ($m['mime'] === 'image/svg+xml') {
        header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'");
    }
    echo $m['data'];
    exit;
}
