<?php

use function App\{e, icon, setting, media_url, tgl, excerpt};

$heroImg = setting('hero_image');
$marquee = setting('marquee_text', 'Desa Maju • Hijau • Sejahtera');
?>
<section class="home-hero" aria-labelledby="hero-title">
    <?php if ($heroImg): ?><img class="home-hero__bg" src="<?= media_url($heroImg) ?>" alt="" fetchpriority="high"><?php endif; ?>
    <svg class="home-hero__hills" viewBox="0 0 1440 400" preserveAspectRatio="none" aria-hidden="true">
        <path d="M0 210 C 240 140 420 170 640 200 S 1080 150 1440 170 V400 H0Z" fill="#1B7A41" opacity=".55"/>
        <path d="M0 290 C 260 230 520 260 760 280 S 1160 240 1440 260 V400 H0Z" fill="#14612F" opacity=".75"/>
        <g fill="#0E4A26">
            <circle cx="150" cy="250" r="34"/><rect x="146" y="250" width="8" height="60"/>
            <circle cx="1180" cy="215" r="38"/><rect x="1176" y="215" width="8" height="70"/>
            <circle cx="1280" cy="240" r="30"/><rect x="1276" y="240" width="8" height="60"/>
        </g>
        <path d="M0 340 C 320 300 640 330 960 330 S 1300 310 1440 320 V400 H0Z" fill="#0C3E21"/>
    </svg>
    <div class="container home-hero__content">
        <span class="kicker"><?= e(setting('region_line')) ?></span>
        <h1 id="hero-title"><?= e(setting('hero_title')) ?></h1>
        <p class="lead"><?= e(setting('hero_subtitle')) ?></p>
        <a href="/profil" class="btn btn--white">Profil Desa <?= icon('arrow-right', 'i-sm') ?></a>

        <div class="hero-cards">
            <a class="hero-card" href="/informasi?bagian=B">
                <span class="hero-card__icon"><?= icon('users') ?></span>
                <div><strong>Penduduk</strong><span><?= \App\angka(setting('stat_penduduk')) ?> jiwa · data kependudukan desa</span></div>
                <?= icon('arrow-right', 'i-sm i-arrow') ?>
            </a>
            <a class="hero-card" href="/profil#wilayah">
                <span class="hero-card__icon"><?= icon('map') ?></span>
                <div><strong>RT/RW</strong><span><?= e(setting('stat_rt')) ?> RT · <?= e(setting('stat_rw')) ?> RW · wilayah dan peta desa</span></div>
                <?= icon('arrow-right', 'i-sm i-arrow') ?>
            </a>
            <a class="hero-card" href="/profil#pelayanan">
                <span class="hero-card__icon"><?= icon('clipboard-list') ?></span>
                <div><strong>Pelayanan</strong><span>Informasi dan layanan desa</span></div>
                <?= icon('arrow-right', 'i-sm i-arrow') ?>
            </a>
        </div>
    </div>
</section>

<div class="marquee" aria-hidden="true">
    <div class="marquee__track">
        <?php for ($i = 0; $i < 6; $i++): ?><span><?= e($marquee) ?> •</span><?php endfor; ?>
    </div>
</div>
<p class="sr-only"><?= e($marquee) ?></p>

<?php if (setting('home_announcement') !== ''): ?>
    <div class="container" style="padding-top:20px"><div class="announce"><?= icon('megaphone') ?><span><?= e(setting('home_announcement')) ?></span></div></div>
<?php endif; ?>

<section class="section section--lg">
    <div class="container">
        <div class="section-head--split">
            <div><span class="kicker">Langkah berikutnya</span><h2>Layanan Cepat</h2></div>
            <p><?= e(setting('quick_intro')) ?></p>
        </div>
        <div class="bento">
            <a class="bento__card bento__card--hero" href="/lapor">
                <span class="tag-andalan">ANDALAN</span>
                <span class="bento__icon-big"><?= icon('megaphone', 'i-lg') ?></span>
                <h3>LaporDesa</h3>
                <p>Sampaikan keluhan dan aspirasi Anda langsung ke pemerintah desa.</p>
                <span class="bento__more">Buka →</span>
            </a>
            <a class="bento__card" href="/cek-status">
                <span class="tile-icon tile-icon--soft"><?= icon('file-search') ?></span>
                <h3>Cek Status</h3>
                <p>Pantau perkembangan laporan dengan nomor tiket.</p>
                <span class="bento__more">Buka →</span>
            </a>
            <a class="bento__card" href="/jelajah">
                <span class="tile-icon tile-icon--soft"><?= icon('compass') ?></span>
                <h3>Jelajah Desa</h3>
                <p>Temukan UMKM, wisata, dan budaya khas desa.</p>
                <span class="bento__more">Buka →</span>
            </a>
            <a class="bento__card bento__card--wide" href="/berita">
                <span class="tile-icon tile-icon--soft"><?= icon('newspaper') ?></span>
                <h3>Berita</h3>
                <p>Kabar, pengumuman, dan kegiatan terbaru.</p>
                <span class="bento__more">Buka →</span>
            </a>
        </div>
    </div>
</section>

<?php if ($news): ?>
<section class="section section--lg section--mint">
    <div class="container">
        <div class="section-head--split">
            <div><span class="kicker">Kabar desa</span><h2>Berita Terbaru</h2></div>
            <a href="/berita" class="btn btn--pill">Lihat Semua <?= icon('arrow-right', 'i-sm') ?></a>
        </div>
        <div class="grid-3">
            <?php foreach (array_slice($news, 0, 3) as $n): ?>
                <a class="card news-card" href="/berita?baca=<?= (int) $n['id'] ?>" data-news="<?= (int) $n['id'] ?>">
                    <img src="<?= media_url($n['image_id']) ?>" alt="" loading="lazy">
                    <div class="news-card__body">
                        <span class="label-up"><?= e($n['category']) ?></span>
                        <h3><?= e($n['title']) ?></h3>
                        <span class="news-card__date"><?= tgl($n['published_at'], true) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
