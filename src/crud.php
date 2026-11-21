<?php

declare(strict_types=1);

namespace App\Crud;

use function App\{all, one, val, q, insert, update, view, redirect, flash, require_admin, abort, store_upload,
    delete_media, paginate, input, slugify};

const ICON_CHOICES = [
    'map', 'landmark', 'users', 'graduation-cap', 'heart', 'activity', 'leaf', 'wallet', 'heart-pulse', 'sprout', 'store', 'route', 'church',
    'chart-column', 'building-2', 'trees', 'droplets', 'zap', 'trash-2', 'construction', 'shield-alert',
    'lightbulb', 'circle-alert', 'waves', 'house', 'tractor', 'baby', 'briefcase', 'factory', 'wheat', 'fish',
];

function entities(): array
{
    return [
        'berita' => [
            'table' => 'news',
            'title' => 'Berita & Kegiatan',
            'singular' => 'berita',
            'icon' => 'newspaper',
            'order' => 'published_at DESC, id DESC',
            'search' => ['title', 'content'],
            'filters' => ['category' => ['Berita', 'Kegiatan', 'Organisasi'], 'status' => ['Terbit', 'Draft']],
            'columns' => ['image_id' => 'Foto', 'title' => 'Judul', 'category' => 'Kategori', 'published_at' => 'Tanggal', 'featured' => 'Utama', 'status' => 'Status'],
            'fields' => [
                'title' => ['label' => 'Judul', 'type' => 'text', 'required' => true, 'max' => 200],
                'category' => ['label' => 'Kategori', 'type' => 'select', 'options' => ['Berita', 'Kegiatan', 'Organisasi'], 'required' => true,
                    'help' => 'Berita = kabar dan informasi desa, Kegiatan = acara/aksi warga, Organisasi = kabar lembaga desa (BPD, PKK, Karang Taruna, kelompok tani, BUMDes).'],
                'featured' => ['label' => 'Jadikan berita utama (tampil paling atas di halaman Berita)', 'type' => 'checkbox'],
                'published_at' => ['label' => 'Tanggal terbit', 'type' => 'date', 'required' => true, 'default' => date('Y-m-d')],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Terbit', 'Draft'], 'default' => 'Terbit'],
                'author' => ['label' => 'Penulis', 'type' => 'text', 'max' => 100, 'default' => 'Admin Desa'],
                'image_id' => ['label' => 'Foto utama', 'type' => 'image'],
                'excerpt' => ['label' => 'Ringkasan', 'type' => 'textarea', 'rows' => 2, 'max' => 300, 'help' => 'Kosongkan untuk dibuat otomatis dari isi berita.'],
                'content' => ['label' => 'Isi berita', 'type' => 'textarea', 'rows' => 12, 'required' => true, 'help' => 'Pisahkan paragraf dengan satu baris kosong.'],
            ],
        ],
        'jelajah' => [
            'table' => 'places',
            'title' => 'Jelajah Desa',
            'singular' => 'tempat / produk',
            'icon' => 'compass',
            'order' => 'category, name',
            'search' => ['name', 'description', 'subcategory'],
            'filters' => ['category' => ['UMKM', 'Wisata', 'Budaya'], 'status' => ['Tampil', 'Sembunyi']],
            'columns' => ['image_id' => 'Foto', 'name' => 'Nama', 'category' => 'Kategori', 'subcategory' => 'Jenis', 'featured' => 'Unggulan', 'status' => 'Status'],
            'fields' => [
                'name' => ['label' => 'Nama', 'type' => 'text', 'required' => true, 'max' => 150],
                'category' => ['label' => 'Kategori', 'type' => 'select', 'options' => ['UMKM', 'Wisata', 'Budaya'], 'required' => true],
                'subcategory' => ['label' => 'Jenis', 'type' => 'text', 'max' => 80, 'help' => 'Contoh: Kuliner, Kerajinan, Agrowisata, Kesenian.'],
                'description' => ['label' => 'Deskripsi', 'type' => 'textarea', 'rows' => 6, 'required' => true],
                'location' => ['label' => 'Lokasi', 'type' => 'text', 'max' => 150, 'help' => 'Contoh: RT 03 / RW 01'],
                'price' => ['label' => 'Harga / tiket', 'type' => 'text', 'max' => 100],
                'hours' => ['label' => 'Jam buka', 'type' => 'text', 'max' => 100],
                'contact' => ['label' => 'Kontak (WA/telepon)', 'type' => 'text', 'max' => 100],
                'image_id' => ['label' => 'Foto', 'type' => 'image'],
                'featured' => ['label' => 'Unggulan (Wisata: tampil di "Sudut Favorit Warga", UMKM: tampil di "Produk Unggulan")', 'type' => 'checkbox'],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Tampil', 'Sembunyi'], 'default' => 'Tampil'],
            ],
        ],
        'galeri' => [
            'table' => 'gallery',
            'title' => 'Galeri',
            'singular' => 'foto',
            'icon' => 'images',
            'order' => "FIELD(status,'Menunggu','Disetujui','Ditolak'), created_at DESC, id DESC",
            'search' => ['title', 'uploader_name'],
            'filters' => ['status' => ['Menunggu', 'Disetujui', 'Ditolak'], 'category' => ['Kegiatan', 'Wisata', 'UMKM', 'Pemerintahan']],
            'columns' => ['image_id' => 'Foto', 'title' => 'Judul', 'category' => 'Kategori', 'uploader_name' => 'Pengirim', 'status' => 'Status'],
            'row_actions' => 'gallery',
            'fields' => [
                'title' => ['label' => 'Judul foto', 'type' => 'text', 'required' => true, 'max' => 150],
                'category' => ['label' => 'Kategori', 'type' => 'select', 'options' => ['Kegiatan', 'Wisata', 'UMKM', 'Pemerintahan'], 'default' => 'Kegiatan'],
                'description' => ['label' => 'Keterangan', 'type' => 'textarea', 'rows' => 3, 'max' => 300],
                'image_id' => ['label' => 'Foto', 'type' => 'image', 'required' => true],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Disetujui', 'Menunggu', 'Ditolak'], 'default' => 'Disetujui'],
                'uploader_name' => ['label' => 'Nama pengirim', 'type' => 'text', 'max' => 100, 'default' => 'Admin Desa'],
                'sort' => ['label' => 'Prioritas tampil (angka besar tampil duluan)', 'type' => 'number', 'default' => '0'],
            ],
        ],
        'perangkat' => [
            'table' => 'officials',
            'title' => 'Perangkat Desa',
            'singular' => 'perangkat desa',
            'icon' => 'users',
            'order' => 'tier, sort, id',
            'columns' => ['photo_id' => 'Foto', 'name' => 'Nama', 'position' => 'Jabatan', 'tier' => 'Tingkat'],
            'labels' => ['tier' => [1 => 'Kepala Desa', 2 => 'Sekretaris', 3 => 'Kasi / Kaur', 4 => 'Kepala Dusun']],
            'fields' => [
                'name' => ['label' => 'Nama', 'type' => 'text', 'required' => true, 'max' => 100],
                'position' => ['label' => 'Jabatan', 'type' => 'text', 'required' => true, 'max' => 100],
                'tier' => ['label' => 'Tingkat dalam bagan', 'type' => 'select', 'options' => [1 => 'Kepala Desa', 2 => 'Sekretaris Desa', 3 => 'Kepala Seksi / Kepala Urusan', 4 => 'Kepala Dusun'], 'default' => '3'],
                'photo_id' => ['label' => 'Foto', 'type' => 'image'],
                'sort' => ['label' => 'Urutan', 'type' => 'number', 'default' => '0'],
            ],
        ],
        'jam' => [
            'table' => 'office_hours',
            'title' => 'Jam Pelayanan',
            'singular' => 'hari',
            'icon' => 'clock',
            'order' => 'sort, id',
            'columns' => ['day' => 'Hari', 'open_time' => 'Buka', 'close_time' => 'Tutup', 'is_closed' => 'Libur'],
            'fields' => [
                'day' => ['label' => 'Hari', 'type' => 'text', 'required' => true, 'max' => 20],
                'open_time' => ['label' => 'Jam buka', 'type' => 'text', 'max' => 10, 'placeholder' => '08.00'],
                'close_time' => ['label' => 'Jam tutup', 'type' => 'text', 'max' => 10, 'placeholder' => '16.00'],
                'is_closed' => ['label' => 'Libur', 'type' => 'checkbox'],
                'sort' => ['label' => 'Urutan', 'type' => 'number', 'default' => '0'],
            ],
        ],
        'wilayah' => [
            'table' => 'map_regions',
            'title' => 'Peta Wilayah (Desa, RW, RT)',
            'singular' => 'wilayah',
            'icon' => 'map',
            'order' => "FIELD(level,'desa','rw','rt'), sort, id",
            'filters' => ['level' => ['desa', 'rw', 'rt']],
            'columns' => ['color' => 'Warna', 'level' => 'Tingkat', 'name' => 'Nama', 'parent_name' => 'Induk', 'leader' => 'Ketua', 'population' => 'Penduduk'],
            'fields' => [
                'level' => ['label' => 'Tingkat', 'type' => 'select', 'options' => ['desa' => 'Desa', 'rw' => 'RW', 'rt' => 'RT'], 'required' => true],
                'name' => ['label' => 'Nama wilayah', 'type' => 'text', 'required' => true, 'max' => 80, 'placeholder' => 'RT 01'],
                'parent_name' => ['label' => 'Induk wilayah', 'type' => 'text', 'max' => 80, 'placeholder' => 'RW 01'],
                'color' => ['label' => 'Warna', 'type' => 'color', 'default' => '#16803C'],
                'leader' => ['label' => 'Nama ketua', 'type' => 'text', 'max' => 100],
                'households' => ['label' => 'Jumlah KK', 'type' => 'number'],
                'population' => ['label' => 'Jumlah penduduk', 'type' => 'number'],
                'geojson' => ['label' => 'Batas wilayah', 'type' => 'polygon', 'required' => true,
                    'help' => 'Klik pada peta untuk menambah titik batas wilayah. Format data: [[lat,lng],[lat,lng],...]'],
                'sort' => ['label' => 'Urutan', 'type' => 'number', 'default' => '0'],
            ],
        ],
        'titik' => [
            'table' => 'map_points',
            'title' => 'Titik Penting di Peta',
            'singular' => 'titik',
            'icon' => 'map-pin',
            'order' => 'sort, id',
            'filters' => ['type' => ['Kantor Desa', 'Pendidikan', 'Kesehatan', 'Fasilitas Umum', 'Wisata', 'UMKM']],
            'columns' => ['name' => 'Nama', 'type' => 'Jenis', 'lat' => 'Lintang', 'lng' => 'Bujur'],
            'fields' => [
                'name' => ['label' => 'Nama tempat', 'type' => 'text', 'required' => true, 'max' => 120],
                'type' => ['label' => 'Jenis', 'type' => 'select', 'options' => ['Kantor Desa', 'Pendidikan', 'Kesehatan', 'Fasilitas Umum', 'Wisata', 'UMKM']],
                'lat' => ['label' => 'Lokasi', 'type' => 'latlng', 'required' => true],
                'lng' => ['label' => 'Bujur', 'type' => 'hidden-number'],
                'description' => ['label' => 'Keterangan', 'type' => 'text', 'max' => 255],
                'sort' => ['label' => 'Urutan', 'type' => 'number', 'default' => '0'],
            ],
        ],
        'kategori-laporan' => [
            'table' => 'report_categories',
            'title' => 'Kategori LaporDesa',
            'singular' => 'kategori',
            'icon' => 'tags',
            'order' => 'sort, id',
            'columns' => ['icon' => 'Ikon', 'name' => 'Nama', 'sort' => 'Urutan'],
            'fields' => [
                'name' => ['label' => 'Nama kategori', 'type' => 'text', 'required' => true, 'max' => 80],
                'icon' => ['label' => 'Ikon', 'type' => 'icon', 'default' => 'circle-alert'],
                'sort' => ['label' => 'Urutan', 'type' => 'number', 'default' => '0'],
            ],
        ],
        'bagian-data' => [
            'table' => 'stat_sections',
            'title' => 'Bagian Informasi Desa (A–H)',
            'singular' => 'bagian',
            'icon' => 'layers',
            'order' => 'sort, code',
            'columns' => ['icon' => 'Ikon', 'code' => 'Kode', 'title' => 'Judul', 'description' => 'Keterangan'],
            'fields' => [
                'code' => ['label' => 'Kode (satu huruf)', 'type' => 'text', 'required' => true, 'max' => 1],
                'title' => ['label' => 'Judul', 'type' => 'text', 'required' => true, 'max' => 120],
                'icon' => ['label' => 'Ikon', 'type' => 'icon', 'default' => 'chart-column'],
                'description' => ['label' => 'Keterangan singkat', 'type' => 'text', 'max' => 255],
                'sort' => ['label' => 'Urutan', 'type' => 'number', 'default' => '0'],
            ],
        ],
        'grafik' => [
            'table' => 'stat_groups',
            'title' => 'Grafik Data Desa',
            'singular' => 'grafik',
            'icon' => 'chart-pie',
            'order' => 'section_id, sort, id',
            'columns' => ['section_id' => 'Bagian', 'title' => 'Judul grafik', 'chart_type' => 'Jenis'],
            'after_save' => '/admin/statistik/{id}',
            'fields' => [
                'section_id' => ['label' => 'Bagian', 'type' => 'relation', 'sql' => "SELECT id, CONCAT(code, '. ', title) AS label FROM stat_sections ORDER BY sort, code", 'required' => true],
                'title' => ['label' => 'Judul grafik', 'type' => 'text', 'required' => true, 'max' => 160],
                'chart_type' => ['label' => 'Jenis tampilan', 'type' => 'select', 'options' => [
                    'bar' => 'Diagram batang', 'hbar' => 'Batang horizontal', 'stacked' => 'Batang bertumpuk', 'pie' => 'Diagram lingkaran',
                    'doughnut' => 'Diagram donat', 'line' => 'Grafik garis', 'cards' => 'Kartu angka', 'table' => 'Tabel'], 'default' => 'bar'],
                'unit' => ['label' => 'Satuan', 'type' => 'text', 'max' => 30, 'placeholder' => 'orang, ha, unit, km'],
                'description' => ['label' => 'Keterangan', 'type' => 'text', 'max' => 255],
                'sort' => ['label' => 'Urutan', 'type' => 'number', 'default' => '0'],
            ],
        ],
    ];
}

function entity(string $key): array
{
    $all = entities();
    if (!isset($all[$key])) {
        abort(404);
    }
    return $all[$key] + ['key' => $key];
}

function index(string $key): void
{
    require_admin();
    $ent = entity($key);
    $where = ['1=1'];
    $params = [];
    foreach ($ent['filters'] ?? [] as $col => $opts) {
        $v = input($col);
        if ($v !== '' && in_array($v, $opts, true)) {
            $where[] = "`$col` = :f_$col";
            $params[":f_$col"] = $v;
        }
    }
    $search = input('cari');
    if ($search !== '' && !empty($ent['search'])) {
        $or = [];
        foreach ($ent['search'] as $i => $col) {
            $or[] = "`$col` LIKE :s$i";
            $params[":s$i"] = '%' . $search . '%';
        }
        $where[] = '(' . implode(' OR ', $or) . ')';
    }
    $w = implode(' AND ', $where);
    $total = (int) val("SELECT COUNT(*) FROM `{$ent['table']}` WHERE $w", $params);
    $pg = paginate($total, 15, (int) input('hal', '1'));
    $rows = all("SELECT * FROM `{$ent['table']}` WHERE $w ORDER BY {$ent['order']} LIMIT {$pg['per']} OFFSET {$pg['offset']}", $params);

    $relations = [];
    foreach ($ent['fields'] as $name => $f) {
        if ($f['type'] === 'relation') {
            foreach (all($f['sql']) as $r) {
                $relations[$name][$r['id']] = $r['label'];
            }
        }
    }

    view('admin/crud_index', [
        'title' => $ent['title'],
        'ent' => $ent,
        'rows' => $rows,
        'pg' => $pg,
        'search' => $search,
        'relations' => $relations,
    ], 'admin');
}

function form(string $key, ?string $id = null): void
{
    require_admin();
    $ent = entity($key);
    $row = [];
    if ($id !== null) {
        $row = one("SELECT * FROM `{$ent['table']}` WHERE id = :id", [':id' => $id]) ?? abort(404);
    }
    $old = $_SESSION['_crud_old'] ?? null;
    unset($_SESSION['_crud_old']);
    if (is_array($old)) {
        $row = array_merge($row, $old);
    }
    $options = [];
    foreach ($ent['fields'] as $name => $f) {
        if ($f['type'] === 'relation') {
            foreach (all($f['sql']) as $r) {
                $options[$name][$r['id']] = $r['label'];
            }
        }
    }
    view('admin/crud_form', [
        'title' => ($id ? 'Ubah ' : 'Tambah ') . $ent['singular'],
        'ent' => $ent,
        'row' => $row,
        'id' => $id,
        'options' => $options,
        'pageScripts' => array_filter($ent['fields'], fn ($f) => in_array($f['type'], ['polygon', 'latlng'], true)) ? ['leaflet'] : [],
    ], 'admin');
}

function save(string $key, ?string $id = null): void
{
    require_admin();
    $ent = entity($key);
    $existing = $id ? (one("SELECT * FROM `{$ent['table']}` WHERE id = :id", [':id' => $id]) ?? abort(404)) : null;

    $data = [];
    $errors = [];
    $newMedia = [];
    $oldMedia = [];

    foreach ($ent['fields'] as $name => $f) {
        $type = $f['type'];
        if ($type === 'image') {
            try {
                $mid = store_upload($name);
            } catch (\RuntimeException $ex) {
                $errors[] = $f['label'] . ': ' . $ex->getMessage();
                continue;
            }
            if ($mid) {
                $data[$name] = $mid;
                $newMedia[] = $mid;
                if ($existing && $existing[$name]) {
                    $oldMedia[] = (int) $existing[$name];
                }
            } elseif (!empty($_POST['remove_' . $name]) && $existing && $existing[$name]) {
                $data[$name] = null;
                $oldMedia[] = (int) $existing[$name];
            }
            $final = array_key_exists($name, $data) ? $data[$name] : ($existing[$name] ?? null);
            if (!empty($f['required']) && !$final) {
                $errors[] = $f['label'] . ' wajib diunggah.';
            }
            continue;
        }
        if ($type === 'checkbox') {
            $data[$name] = !empty($_POST[$name]) ? 1 : 0;
            continue;
        }
        $v = isset($_POST[$name]) && is_string($_POST[$name]) ? trim($_POST[$name]) : '';
        if ($type === 'latlng') {
            $lat = trim((string) ($_POST['lat'] ?? ''));
            $lng = trim((string) ($_POST['lng'] ?? ''));
            if (!is_numeric($lat) || !is_numeric($lng)) {
                $errors[] = 'Tentukan lokasi dengan klik pada peta.';
            }
            $data['lat'] = (float) $lat;
            $data['lng'] = (float) $lng;
            continue;
        }
        if ($type === 'hidden-number') {
            continue;
        }
        if ((!empty($f['required']) || ($type === 'select' && isset($f['default']))) && $v === '') {
            $errors[] = $f['label'] . ' wajib diisi.';
        }
        if (isset($f['max']) && mb_strlen($v) > $f['max']) {
            $errors[] = $f['label'] . " maksimal {$f['max']} karakter.";
        }
        if ($type === 'number' && $v !== '' && !is_numeric($v)) {
            $errors[] = $f['label'] . ' harus berupa angka.';
        }
        if ($type === 'select' && $v !== '') {
            $keys = array_is_list($f['options']) ? $f['options'] : array_map('strval', array_keys($f['options']));
            if (!in_array($v, $keys, true)) {
                $errors[] = $f['label'] . ' tidak valid.';
            }
        }
        if ($type === 'polygon' && $v !== '') {
            $pts = json_decode($v, true);
            $valid = is_array($pts) && count($pts) >= 3;
            foreach ((array) $pts as $pt) {
                if (!is_array($pt) || count($pt) !== 2 || !is_numeric($pt[0]) || !is_numeric($pt[1])) {
                    $valid = false;
                }
            }
            if (!$valid) {
                $errors[] = 'Batas wilayah minimal 3 titik dengan format [[lat,lng],...].';
            }
        }
        if ($type === 'color' && $v !== '' && !preg_match('/^#[0-9a-fA-F]{6}$/', $v)) {
            $errors[] = 'Warna tidak valid.';
        }
        if ($v === '') {
            $data[$name] = isset($f['default']) && in_array($type, ['number', 'select'], true) ? (string) $f['default'] : null;
        } else {
            $data[$name] = $v;
        }
    }

    if ($ent['table'] === 'stat_sections' && isset($data['code'])) {
        $data['code'] = strtoupper($data['code']);
        $dupe = val('SELECT id FROM stat_sections WHERE code = :c AND id <> :id', [':c' => $data['code'], ':id' => (int) $id]);
        if ($dupe) {
            $errors[] = 'Kode bagian sudah dipakai.';
        }
    }

    if ($errors) {
        foreach ($newMedia as $m) {
            delete_media($m);
        }
        $_SESSION['_crud_old'] = array_diff_key($data, array_flip(array_keys(array_filter($ent['fields'], fn ($f) => $f['type'] === 'image'))));
        flash('error', implode(' ', $errors));
        redirect('/admin/kelola/' . $key . ($id ? '/' . $id : '/baru'));
    }

    if ($ent['table'] === 'news') {
        if (empty($data['excerpt'])) {
            $data['excerpt'] = \App\excerpt($data['content'] ?? '', 180);
        }
        if (!$existing || $existing['title'] !== $data['title']) {
            $base = slugify($data['title']);
            $slug = $base;
            $i = 2;
            while (val('SELECT 1 FROM news WHERE slug = :s AND id <> :id', [':s' => $slug, ':id' => (int) $id])) {
                $slug = $base . '-' . $i++;
            }
            $data['slug'] = $slug;
        }
    }

    if ($existing) {
        update($ent['table'], $data, (int) $id);
        $newId = (int) $id;
    } else {
        $newId = insert($ent['table'], $data);
    }
    foreach ($oldMedia as $m) {
        delete_media($m);
    }

    flash('success', ucfirst($ent['singular']) . ' berhasil disimpan.');
    if (!$existing && isset($ent['after_save'])) {
        redirect(str_replace('{id}', (string) $newId, $ent['after_save']));
    }
    redirect('/admin/kelola/' . $key);
}

function delete(string $key, string $id): void
{
    require_admin();
    $ent = entity($key);
    $row = one("SELECT * FROM `{$ent['table']}` WHERE id = :id", [':id' => $id]);
    if ($row) {
        q("DELETE FROM `{$ent['table']}` WHERE id = :id", [':id' => $id]);
        foreach ($ent['fields'] as $name => $f) {
            if ($f['type'] === 'image' && !empty($row[$name])) {
                delete_media((int) $row[$name]);
            }
        }
        flash('success', ucfirst($ent['singular']) . ' berhasil dihapus.');
    }
    redirect('/admin/kelola/' . $key);
}
