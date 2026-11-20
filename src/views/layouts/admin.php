<?php

use function App\{e, icon, active, user, take_flash, csrf_field, val};

$u = user();
$pageScripts = $pageScripts ?? [];
$badge = [
    '/admin/laporan' => (int) val("SELECT COUNT(*) FROM reports WHERE status = 'Menunggu'"),
    '/admin/kelola/galeri' => (int) val("SELECT COUNT(*) FROM gallery WHERE status = 'Menunggu'"),
    '/admin/pesan' => (int) val('SELECT COUNT(*) FROM messages WHERE is_read = 0'),
];
$menu = [
    'Utama' => [
        ['/admin', 'Dasbor', 'layout-dashboard'],
        ['/admin/laporan', 'Laporan Warga', 'megaphone'],
        ['/admin/pesan', 'Pesan Masuk', 'inbox'],
    ],
    'Konten' => [
        ['/admin/kelola/berita', 'Berita & Kegiatan', 'newspaper'],
        ['/admin/kelola/jelajah', 'Jelajah Desa', 'compass'],
        ['/admin/kelola/galeri', 'Galeri', 'images'],
    ],
    'Data Desa' => [
        ['/admin/profil', 'Profil & Geografis', 'landmark'],
        ['/admin/statistik', 'Data Informasi A–H', 'chart-column'],
        ['/admin/kelola/perangkat', 'Perangkat Desa', 'users'],
        ['/admin/kelola/wilayah', 'Peta Wilayah', 'map'],
        ['/admin/kelola/titik', 'Titik Penting', 'map-pin'],
    ],
    'Pengaturan' => [
        ['/admin/pengaturan', 'Beranda & Kontak', 'settings'],
        ['/admin/kelola/jam', 'Jam Pelayanan', 'clock'],
        ['/admin/kelola/kategori-laporan', 'Kategori Laporan', 'tags'],
        ['/admin/pengguna', 'Pengguna', 'user-cog'],
    ],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= e($title ?? 'Admin') ?> — CMS EcoVillage</title>
    <link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
    <?php if (in_array('leaflet', $pageScripts, true)): ?><link rel="stylesheet" href="/assets/vendor/leaflet/leaflet.css"><?php endif; ?>
    <link rel="preload" href="/assets/fonts/plus-jakarta-sans.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="/assets/css/app.css?v=3">
    <link rel="stylesheet" href="/assets/css/admin.css?v=3">
    <script>try { var t = localStorage.getItem('ecovillage-theme'); if (t) document.documentElement.setAttribute('data-theme', t); } catch (e) {}</script>
</head>
<body class="admin">
<a class="skip-link" href="#konten">Lewati ke konten utama</a>
<div class="admin-shell">
    <aside class="admin-side drawer" id="drawer" aria-hidden="true">
        <div class="drawer__backdrop" data-drawer-close></div>
        <nav class="drawer__panel admin-side__panel" aria-label="Menu admin">
            <div class="drawer__head">
                <a href="/admin" class="brand"><span class="brand__logo"><?= icon('leaf') ?></span><span class="brand__text"><span class="brand__name">CMS Desa</span><span class="brand__sub">Marga Mulya</span></span></a>
                <button class="icon-btn admin-close" type="button" data-drawer-close aria-label="Tutup menu"><?= icon('x') ?></button>
            </div>
            <?php foreach ($menu as $group => $items): ?>
                <div class="admin-side__group"><?= e($group) ?></div>
                <?php foreach ($items as [$href, $label, $ic]): ?>
                    <a class="drawer__link" href="<?= $href ?>"<?= $href === '/admin' ? (\App\current_path() === '/admin' ? ' aria-current="page"' : '') : active($href) ?>>
                        <?= icon($ic) ?> <span style="flex:1"><?= e($label) ?></span>
                        <?php if (!empty($badge[$href])): ?><span class="badge badge--warn"><?= $badge[$href] ?></span><?php endif; ?>
                    </a>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <hr>
            <a class="drawer__link" href="/" target="_blank"><?= icon('external-link') ?> Lihat website</a>
        </nav>
    </aside>

    <div class="admin-main">
        <header class="admin-top">
            <button class="icon-btn nav-toggle admin-menu-btn" type="button" data-drawer-open aria-label="Buka menu" aria-controls="drawer" aria-expanded="false"><?= icon('menu') ?></button>
            <h1 class="admin-top__title"><?= e($title ?? 'Admin') ?></h1>
            <button class="icon-btn" type="button" data-theme-toggle aria-label="Ganti tema"><?= icon('moon') ?></button>
            <form method="post" action="/keluar">
                <?= csrf_field() ?>
                <button class="btn btn--ghost btn--sm" type="submit" title="Keluar"><?= icon('log-out', 'i-sm') ?> <span class="hide-sm"><?= e($u['name'] ?? '') ?></span></button>
            </form>
        </header>
        <main id="konten" class="admin-content">
            <?php foreach (take_flash() as $f): ?>
                <div class="alert alert--<?= e($f['type']) ?>" role="status"><?= icon($f['type'] === 'error' ? 'circle-alert' : 'circle-check') ?><span><?= e($f['msg']) ?></span></div>
            <?php endforeach; ?>
            <?= $content ?>
        </main>
    </div>
</div>
<div class="modal lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Pratinjau foto">
    <div class="modal__box">
        <button class="icon-btn modal__close" type="button" data-modal-close aria-label="Tutup"><?= icon('x') ?></button>
        <figure style="margin:0"><img alt="" data-lightbox-img><figcaption data-lightbox-cap></figcaption></figure>
    </div>
</div>
<?php if (in_array('chart', $pageScripts, true)): ?><script src="/assets/vendor/chart.umd.min.js"></script><?php endif; ?>
<?php if (in_array('leaflet', $pageScripts, true)): ?><script src="/assets/vendor/leaflet/leaflet.js"></script><?php endif; ?>
<script src="/assets/js/app.js?v=3"></script>
</body>
</html>
