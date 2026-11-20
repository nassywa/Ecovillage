<?php

declare(strict_types=1);

namespace App\Admin;

use function App\{all, one, val, q, insert, update, view, redirect, flash, require_admin, abort, store_upload,
    delete_media, paginate, input, setting, save_setting, user, csrf_token};

/** Daftar field pengaturan yang bisa diubah admin, dikelompokkan per kartu */
function settings_schema(): array
{
    return [
        'Identitas & Beranda' => [
            'site_name' => ['Nama situs', 'text'],
            'village_name' => ['Nama desa', 'text'],
            'region_line' => ['Kecamatan, Kabupaten, Provinsi', 'text'],
            'hero_title' => ['Judul besar beranda', 'text'],
            'hero_subtitle' => ['Subjudul beranda', 'text'],
            'hero_image' => ['Foto utama beranda', 'image'],
            'home_announcement' => ['Pengumuman singkat (kosongkan bila tidak ada)', 'text'],
        ],
        'Angka Ringkas (Beranda & Profil)' => [
            'stat_penduduk' => ['Jumlah penduduk', 'number'],
            'stat_kk' => ['Jumlah kepala keluarga', 'number'],
            'stat_rw' => ['Jumlah RW', 'number'],
            'stat_rt' => ['Jumlah RT', 'number'],
            'stat_pelayanan' => ['Indeks kepuasan pelayanan (%)', 'number'],
        ],
        'Kontak & Lokasi' => [
            'contact_address' => ['Alamat kantor desa', 'textarea'],
            'contact_phone' => ['Telepon', 'text'],
            'contact_whatsapp' => ['WhatsApp (format 62...)', 'text'],
            'contact_email' => ['Email', 'text'],
            'map_lat' => ['Lintang pusat peta', 'text'],
            'map_lng' => ['Bujur pusat peta', 'text'],
            'map_zoom' => ['Zoom awal peta (12–17)', 'number'],
            'office_lat' => ['Lintang kantor desa', 'text'],
            'office_lng' => ['Bujur kantor desa', 'text'],
        ],
        'Media Sosial' => [
            'social_facebook' => ['Facebook', 'text'],
            'social_tiktok' => ['TikTok', 'text'],
            'social_youtube' => ['YouTube', 'text'],
            'social_instagram' => ['Instagram', 'text'],
        ],
        'Teks Halaman' => [
            'jelajah_intro' => ['Pengantar halaman Jelajah Desa', 'textarea'],
            'gallery_intro' => ['Pengantar halaman Galeri', 'textarea'],
            'lapor_intro' => ['Pengantar halaman LaporDesa', 'textarea'],
            'footer_about' => ['Teks singkat di footer', 'textarea'],
        ],
        'Keamanan' => [
            'admin_register_code' => ['Kode pendaftaran admin (dibutuhkan saat daftar sebagai admin)', 'text'],
        ],
    ];
}

function profile_schema(): array
{
    return [
        'Tentang Desa' => [
            'profile_image' => ['Foto gerbang / ikon desa', 'image'],
            'profil_tentang' => ['Tentang desa (ringkas)', 'textarea'],
            'profil_sejarah' => ['Sejarah desa', 'textarea-lg'],
            'profil_visi' => ['Visi', 'textarea'],
            'profil_misi' => ['Misi (satu baris = satu poin)', 'textarea'],
            'office_image' => ['Foto kantor desa', 'image'],
        ],
        'Kondisi Geografis' => [
            'geo_luas' => ['Luas wilayah (ha)', 'number'],
            'geo_utara' => ['Batas utara', 'text'],
            'geo_timur' => ['Batas timur', 'text'],
            'geo_selatan' => ['Batas selatan', 'text'],
            'geo_barat' => ['Batas barat', 'text'],
            'geo_ketinggian' => ['Ketinggian (mdpl)', 'text'],
            'geo_iklim' => ['Iklim / curah hujan', 'text'],
            'geo_jarak' => ['Jarak ke pusat kecamatan / kabupaten', 'text'],
        ],
    ];
}

/* ---------------------------------------------------------
 * DASHBOARD
 * ------------------------------------------------------- */
function dashboard(): void
{
    require_admin();
    $counts = one("SELECT
        (SELECT COUNT(*) FROM reports) reports_total,
        (SELECT COUNT(*) FROM reports WHERE status IN ('Menunggu','Diterima','Diproses')) reports_open,
        (SELECT COUNT(*) FROM reports WHERE status = 'Menunggu') reports_new,
        (SELECT COUNT(*) FROM gallery WHERE status = 'Menunggu') gallery_pending,
        (SELECT COUNT(*) FROM messages WHERE is_read = 0) messages_unread,
        (SELECT COUNT(*) FROM news WHERE status = 'Terbit') news_total,
        (SELECT COUNT(*) FROM places WHERE status = 'Tampil') places_total,
        (SELECT COUNT(*) FROM users WHERE role = 'warga') users_total");
    view('admin/dashboard', [
        'title' => 'Dasbor',
        'counts' => $counts,
        'byStatus' => all('SELECT status, COUNT(*) n FROM reports GROUP BY status'),
        'byCategory' => all('SELECT category, COUNT(*) n FROM reports GROUP BY category ORDER BY n DESC'),
        'latestReports' => all('SELECT id, ticket, category, location, status, created_at FROM reports ORDER BY created_at DESC, id DESC LIMIT 6'),
        'pendingGallery' => all("SELECT id, title, uploader_name, image_id, created_at FROM gallery WHERE status = 'Menunggu' ORDER BY created_at DESC LIMIT 4"),
        'pageScripts' => ['chart'],
    ], 'admin');
}

/* ---------------------------------------------------------
 * PENGATURAN & PROFIL (key-value)
 * ------------------------------------------------------- */
function settings_form(): void
{
    require_admin();
    view('admin/settings', ['title' => 'Pengaturan Situs', 'schema' => settings_schema(), 'action' => '/admin/pengaturan'], 'admin');
}

function settings_save(): void
{
    require_admin();
    save_schema(settings_schema());
    flash('success', 'Pengaturan berhasil disimpan.');
    redirect('/admin/pengaturan');
}

function profile_form(): void
{
    require_admin();
    view('admin/settings', ['title' => 'Profil & Geografis Desa', 'schema' => profile_schema(), 'action' => '/admin/profil'], 'admin');
}

function profile_save(): void
{
    require_admin();
    save_schema(profile_schema());
    flash('success', 'Profil desa berhasil disimpan.');
    redirect('/admin/profil');
}

function save_schema(array $schema): void
{
    $errors = [];
    foreach ($schema as $fields) {
        foreach ($fields as $key => [$label, $type]) {
            if ($type === 'image') {
                try {
                    $mid = store_upload($key);
                } catch (\RuntimeException $e) {
                    $errors[] = $label . ': ' . $e->getMessage();
                    continue;
                }
                if ($mid) {
                    $old = (int) setting($key);
                    save_setting($key, (string) $mid);
                    if ($old) {
                        delete_media($old);
                    }
                }
                continue;
            }
            if (!array_key_exists($key, $_POST)) {
                continue;
            }
            $v = trim((string) $_POST[$key]);
            if ($type === 'number' && $v !== '' && !is_numeric(str_replace(['.', ','], ['', '.'], $v))) {
                $errors[] = $label . ' harus berupa angka.';
                continue;
            }
            if ($type === 'number') {
                $v = str_replace('.', '', $v);
            }
            save_setting($key, $v);
        }
    }
    if ($errors) {
        flash('error', implode(' ', $errors));
    }
}

/* ---------------------------------------------------------
 * DATA STATISTIK (isi grafik)
 * ------------------------------------------------------- */
function stats_index(): void
{
    require_admin();
    $sections = all('SELECT * FROM stat_sections ORDER BY sort, code');
    $groups = all('SELECT g.*, (SELECT COUNT(*) FROM stat_items i WHERE i.group_id = g.id) items FROM stat_groups g ORDER BY sort, id');
    $by = [];
    foreach ($groups as $g) {
        $by[$g['section_id']][] = $g;
    }
    view('admin/stats_index', ['title' => 'Data Informasi Desa (A–H)', 'sections' => $sections, 'by' => $by], 'admin');
}

function stats_edit(string $id): void
{
    require_admin();
    $group = one('SELECT g.*, s.code, s.title section_title FROM stat_groups g JOIN stat_sections s ON s.id = g.section_id WHERE g.id = :id', [':id' => $id]) ?? abort(404);
    view('admin/stats_edit', [
        'title' => $group['title'],
        'group' => $group,
        'items' => all('SELECT * FROM stat_items WHERE group_id = :g ORDER BY sort, id', [':g' => $id]),
        'pageScripts' => ['chart'],
    ], 'admin');
}

function stats_save(string $id): void
{
    require_admin();
    $group = one('SELECT id FROM stat_groups WHERE id = :id', [':id' => $id]) ?? abort(404);
    $labels = $_POST['label'] ?? [];
    $series = $_POST['series'] ?? [];
    $values = $_POST['value'] ?? [];
    if (!is_array($labels) || !is_array($series) || !is_array($values)) {
        abort(400);
    }
    $rows = [];
    foreach ($labels as $i => $label) {
        $label = trim((string) $label);
        $val = str_replace(',', '.', trim((string) ($values[$i] ?? '')));
        if ($label === '' && $val === '') {
            continue;
        }
        if ($label === '' || !is_numeric($val)) {
            flash('error', 'Setiap baris wajib berisi label dan angka yang valid (baris ' . ($i + 1) . ').');
            redirect('/admin/statistik/' . $id);
        }
        $rows[] = [mb_substr($label, 0, 120), trim((string) ($series[$i] ?? '')) ?: null, (float) $val];
    }
    $pdo = \App\db();
    $pdo->beginTransaction();
    q('DELETE FROM stat_items WHERE group_id = :g', [':g' => $group['id']]);
    foreach ($rows as $n => [$label, $s, $v]) {
        insert('stat_items', ['group_id' => $group['id'], 'label' => $label, 'series' => $s, 'value' => $v, 'sort' => $n]);
    }
    $pdo->commit();
    flash('success', 'Data grafik berhasil disimpan (' . count($rows) . ' baris).');
    redirect('/admin/statistik/' . $id);
}

/* ---------------------------------------------------------
 * LAPORAN WARGA
 * ------------------------------------------------------- */
function reports_index(): void
{
    require_admin();
    $status = input('status');
    $cat = input('kategori');
    $search = input('cari');
    $where = '1=1';
    $p = [];
    if (in_array($status, \App\REPORT_STATUSES, true)) {
        $where .= ' AND status = :s';
        $p[':s'] = $status;
    }
    if ($cat !== '') {
        $where .= ' AND category = :c';
        $p[':c'] = $cat;
    }
    if ($search !== '') {
        $where .= ' AND (ticket LIKE :q1 OR location LIKE :q2 OR description LIKE :q3 OR reporter_name LIKE :q4)';
        $p += [':q1' => "%$search%", ':q2' => "%$search%", ':q3' => "%$search%", ':q4' => "%$search%"];
    }
    $total = (int) val("SELECT COUNT(*) FROM reports WHERE $where", $p);
    $pg = paginate($total, 15, (int) input('hal', '1'));
    view('admin/reports', [
        'title' => 'Laporan Warga (LaporDesa)',
        'rows' => all("SELECT * FROM reports WHERE $where ORDER BY FIELD(status,'Menunggu','Diterima','Diproses','Selesai','Ditolak'), created_at DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}", $p),
        'pg' => $pg,
        'status' => $status,
        'cat' => $cat,
        'search' => $search,
        'categories' => all('SELECT name FROM report_categories ORDER BY sort, id'),
    ], 'admin');
}

function report_show(string $id): void
{
    require_admin();
    $r = one('SELECT r.*, u.email user_email FROM reports r LEFT JOIN users u ON u.id = r.user_id WHERE r.id = :id', [':id' => $id]) ?? abort(404);
    view('admin/report_show', [
        'title' => 'Laporan #' . $r['ticket'],
        'r' => $r,
        'history' => all('SELECT * FROM report_updates WHERE report_id = :id ORDER BY created_at, id', [':id' => $id]),
    ], 'admin');
}

function report_update(string $id): void
{
    require_admin();
    $r = one('SELECT * FROM reports WHERE id = :id', [':id' => $id]) ?? abort(404);
    if (input('action') === 'hapus') {
        q('DELETE FROM reports WHERE id = :id', [':id' => $id]);
        delete_media($r['photo_id'] ? (int) $r['photo_id'] : null);
        flash('success', 'Laporan #' . $r['ticket'] . ' dihapus.');
        redirect('/admin/laporan');
    }
    $status = input('status');
    $note = input('note');
    if (!in_array($status, \App\REPORT_STATUSES, true)) {
        flash('error', 'Status tidak valid.');
        redirect('/admin/laporan/' . $id);
    }
    $data = ['status' => $status, 'admin_note' => $note !== '' ? $note : $r['admin_note']];
    if (in_array($status, ['Diterima', 'Diproses', 'Selesai'], true) && !$r['accepted_at']) {
        $data['accepted_at'] = date('Y-m-d H:i:s');
    }
    if ($status === 'Selesai' && !$r['finished_at']) {
        $data['finished_at'] = date('Y-m-d H:i:s');
    }
    update('reports', $data, (int) $id);
    if ($status !== $r['status'] || $note !== '') {
        insert('report_updates', ['report_id' => $id, 'status' => $status, 'note' => $note !== '' ? $note : null]);
    }
    flash('success', 'Status laporan diperbarui menjadi "' . $status . '".');
    redirect('/admin/laporan/' . $id);
}

/* ---------------------------------------------------------
 * GALERI: persetujuan foto warga
 * ------------------------------------------------------- */
function gallery_status(string $id): void
{
    require_admin();
    $status = input('status');
    if (!in_array($status, ['Disetujui', 'Ditolak', 'Menunggu'], true)) {
        abort(400);
    }
    q('UPDATE gallery SET status = :s WHERE id = :id', [':s' => $status, ':id' => $id]);
    flash('success', $status === 'Disetujui' ? 'Foto disetujui dan sudah tampil di galeri.' : 'Status foto diubah menjadi ' . $status . '.');
    \App\back('/admin/kelola/galeri');
}

/* ---------------------------------------------------------
 * PESAN DARI HALAMAN KONTAK
 * ------------------------------------------------------- */
function messages_index(): void
{
    require_admin();
    $total = (int) val('SELECT COUNT(*) FROM messages');
    $pg = paginate($total, 15, (int) input('hal', '1'));
    view('admin/messages', [
        'title' => 'Pesan Masuk',
        'rows' => all("SELECT * FROM messages ORDER BY is_read, created_at DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}"),
        'pg' => $pg,
    ], 'admin');
}

function message_action(string $id): void
{
    require_admin();
    if (input('action') === 'hapus') {
        q('DELETE FROM messages WHERE id = :id', [':id' => $id]);
        flash('success', 'Pesan dihapus.');
    } else {
        q('UPDATE messages SET is_read = 1 - is_read WHERE id = :id', [':id' => $id]);
    }
    redirect('/admin/pesan');
}

/* ---------------------------------------------------------
 * PENGGUNA
 * ------------------------------------------------------- */
function users_index(): void
{
    require_admin();
    $total = (int) val('SELECT COUNT(*) FROM users');
    $pg = paginate($total, 20, (int) input('hal', '1'));
    view('admin/users', [
        'title' => 'Pengguna',
        'rows' => all("SELECT u.*, (SELECT COUNT(*) FROM reports r WHERE r.user_id = u.id) reports,
                       (SELECT COUNT(*) FROM gallery g WHERE g.user_id = u.id) photos
                       FROM users u ORDER BY role, created_at DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}"),
        'pg' => $pg,
    ], 'admin');
}

function user_action(string $id): void
{
    $me = require_admin();
    if ((int) $id === (int) $me['id']) {
        flash('error', 'Anda tidak dapat mengubah akun Anda sendiri dari sini.');
        redirect('/admin/pengguna');
    }
    $action = input('action');
    if ($action === 'hapus') {
        q('DELETE FROM users WHERE id = :id', [':id' => $id]);
        flash('success', 'Pengguna dihapus.');
    } elseif (in_array($action, ['admin', 'warga'], true)) {
        q('UPDATE users SET role = :r WHERE id = :id', [':r' => $action, ':id' => $id]);
        flash('success', 'Peran pengguna diubah menjadi ' . $action . '.');
    }
    redirect('/admin/pengguna');
}
