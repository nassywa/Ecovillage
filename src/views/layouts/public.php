<?php

use function App\{e, icon, setting, active, user, take_flash, csrf_field, social_links, brand_icon, office_hours};

$village = setting('village_name', 'Desa Marga Mulya');
$u = user();
$pageScripts = $pageScripts ?? [];
$nav = [
    ['/', 'Beranda', 'house'],
    ['/profil', 'Profil Desa', 'landmark'],
    ['/informasi', 'Informasi Desa', 'chart-column'],
    ['/berita', 'Berita & Kegiatan', 'newspaper'],
    ['/jelajah', 'Sudut Istimewa', 'compass'],
    ['/galeri', 'Galeri', 'images'],
    ['/lapor', 'Lapor Desa', 'megaphone'],
    ['/kontak', 'Kontak', 'phone'],
];
$footerNav = [['/', 'Beranda'], ['/profil', 'Profil Desa'], ['/informasi', 'Informasi Desa'], ['/berita', 'Berita & Kegiatan'], ['/jelajah', 'Sudut Istimewa'], ['/galeri', 'Galeri'], ['/lapor', 'Lapor Desa'], ['/cek-status', 'Cek Lapor Desa'], ['/kontak', 'Kontak']];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e(($title ?? '') ? $title . ' — ' . $village : $village . ' — Website Resmi') ?></title>
    <meta name="description" content="<?= e($description ?? setting('footer_about', 'Website resmi ' . $village)) ?>">
    <meta name="theme-color" content="#0B3D22">
    <meta property="og:title" content="<?= e(($title ?? 'Beranda') . ' — ' . $village) ?>">
    <meta property="og:type" content="website">
    <link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
    <link rel="preload" href="/assets/fonts/inter.woff2" as="font" type="font/woff2" crossorigin>
    <?php if (in_array('leaflet', $pageScripts, true)): ?>
        <link rel="stylesheet" href="/assets/vendor/leaflet/leaflet.css">
    <?php endif; ?>
    <link rel="stylesheet" href="/assets/css/app.css?v=8">
    <script>
        try { var t = localStorage.getItem('ecovillage-theme'); if (t === 'dark' || t === 'light') document.documentElement.setAttribute('data-theme', t); } catch (e) {}
        if (window.matchMedia && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) document.documentElement.classList.add('motion');
    </script>
</head>
<body>
<a class="skip-link" href="#konten">Lewati ke konten utama</a>

<header class="site-header" data-header>
    <div class="container site-header__inner">
        <a href="/" class="brand" aria-label="<?= e($village) ?> — Beranda">
            <span class="brand__logo--round"><?= icon('leaf', 'i-sm') ?></span>
            <span class="brand__text">
                <span class="brand__name"><?= e($village) ?></span>
                <span class="brand__sub"><?= e(setting('region_short', 'Kab. Kotawaringin Barat')) ?></span>
            </span>
        </a>

        <nav class="nav" aria-label="Menu utama">
            <?php foreach ($nav as [$href, $label]): ?>
                <a href="<?= $href ?>"<?= active($href) ?>><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="header-actions">
            <button class="icon-btn icon-btn--ghost" type="button" data-theme-toggle aria-label="Ganti tema"><?= icon('sun') ?></button>
            <?php if ($u): ?>
                <a href="<?= $u['role'] === 'admin' ? '/admin' : '/cek-status' ?>" class="btn btn--white btn--sm header-cta"><?= icon('circle-user', 'i-sm') ?> <?= e(strtok($u['name'], ' ')) ?></a>
            <?php else: ?>
                <a href="/masuk" class="btn btn--white btn--sm header-cta">Masuk</a>
            <?php endif; ?>
            <button class="icon-btn icon-btn--ghost nav-toggle" type="button" data-drawer-open aria-label="Buka menu" aria-controls="drawer" aria-expanded="false"><?= icon('menu') ?></button>
        </div>
    </div>
</header>

<div class="drawer" id="drawer" aria-hidden="true">
    <div class="drawer__backdrop" data-drawer-close></div>
    <nav class="drawer__panel" aria-label="Menu">
        <div class="drawer__head">
            <strong>Menu</strong>
            <button class="icon-btn" type="button" data-drawer-close aria-label="Tutup menu"><?= icon('x') ?></button>
        </div>
        <?php foreach ($nav as [$href, $label, $ic]): ?>
            <a class="drawer__link" href="<?= $href ?>"<?= active($href) ?>><?= icon($ic) ?> <?= e($label) ?></a>
        <?php endforeach; ?>
        <a class="drawer__link" href="/cek-status"<?= active('/cek-status') ?>><?= icon('search-check') ?> Cek Lapor Desa</a>
        <hr>
        <?php if ($u): ?>
            <?php if ($u['role'] === 'admin'): ?><a class="drawer__link" href="/admin"><?= icon('layout-dashboard') ?> Panel Admin</a><?php endif; ?>
            <form method="post" action="/keluar">
                <?= csrf_field() ?>
                <button class="btn btn--ghost btn--block" type="submit"><?= icon('log-out') ?> Keluar (<?= e($u['name']) ?>)</button>
            </form>
        <?php else: ?>
            <a class="btn btn--block btn--pill" href="/masuk"><?= icon('log-in') ?> Masuk / Daftar</a>
        <?php endif; ?>
    </nav>
</div>

<?php $flashes = take_flash(); if ($flashes): ?>
    <div class="toasts" role="status" aria-live="polite">
        <?php foreach ($flashes as $f): ?>
            <div class="alert alert--<?= e($f['type']) ?> toast"><?= icon($f['type'] === 'error' ? 'circle-alert' : ($f['type'] === 'success' ? 'circle-check' : 'info')) ?><span><?= e($f['msg']) ?></span><button type="button" class="toast__close" aria-label="Tutup" data-toast-close>×</button></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<main id="konten">
    <?= $content ?>
</main>

<?php if (empty($noCta)): ?>
<section class="cta-band" aria-labelledby="cta-title">
    <div class="container"><div class="cta-band__inner">
        <span class="kicker">Layanan Warga</span>
        <h2 id="cta-title"><?= e(setting('cta_title', 'Ada yang bisa kami bantu?')) ?></h2>
        <p><?= e(setting('cta_text')) ?></p>
        <div class="cta-band__actions">
            <a href="/lapor" class="btn btn--white"><?= icon('megaphone', 'i-sm') ?> Lapor Desa <?= icon('arrow-right', 'i-sm') ?></a>
            <a href="/kontak" class="btn btn--outline-white">Hubungi Kami <?= icon('arrow-right', 'i-sm') ?></a>
        </div>
    </div></div>
</section>
<?php endif; ?>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <div class="footer-brand"><span class="brand__logo--round"><?= icon('leaf', 'i-sm') ?></span> <?= e($village) ?></div>
                <p class="small muted"><?= e(setting('footer_about')) ?></p>
                <div class="footer-social">
                    <?php foreach (social_links() as $k => [$label, $url]): ?>
                        <a href="<?= e($url) ?>" target="_blank" rel="noopener" aria-label="<?= e($label) ?>"><?= brand_icon($k) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div>
                <h3>Navigasi</h3>
                <ul class="footer-links">
                    <?php foreach ($footerNav as [$href, $label]): ?><li><a href="<?= $href ?>"><?= e($label) ?></a></li><?php endforeach; ?>
                </ul>
            </div>
            <div>
                <h3>Kontak</h3>
                <ul class="footer-contact">
                    <li><?= icon('map-pin', 'i-sm') ?><span><?= e(setting('contact_address')) ?></span></li>
                    <li><?= icon('mail', 'i-sm') ?><a href="mailto:<?= e(setting('contact_email')) ?>"><?= e(setting('contact_email')) ?></a></li>
                    <li><?= icon('phone', 'i-sm') ?><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', setting('contact_phone'))) ?>"><?= e(setting('contact_phone')) ?></a></li>
                </ul>
            </div>
            <div>
                <h3>Jam Pelayanan</h3>
                <ul class="footer-hours">
                    <?php foreach (office_hours() as $h): ?>
                        <li><span><?= e($h['day']) ?></span><span class="<?= $h['is_closed'] ? 'muted' : '' ?>"><?= $h['is_closed'] ? 'Libur' : e($h['open_time'] . '–' . $h['close_time']) . ' WIB' ?></span></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© <?= date('Y') ?> Pemerintah <?= e($village) ?>. Seluruh hak dilindungi.</span>
            <span>Seluruh data pada situs ini dikelola oleh admin desa melalui CMS.</span>
        </div>
    </div>
</footer>

<div class="modal" id="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="modal__box">
        <button class="icon-btn modal__close" type="button" data-modal-close aria-label="Tutup"><?= icon('x') ?></button>
        <div data-modal-content></div>
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
<script src="/assets/js/app.js?v=8"></script>
</body>
</html>
