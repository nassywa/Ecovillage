<?php

use function App\{e, icon, media_url, tgl, render, excerpt, qs};

$cats = ['Berita', 'Kegiatan', 'Pengumuman'];
$showFeature = $featured && $pg['page'] === 1 && $search === '' && ($cat === '' || $cat === $featured['category']);
?>
<?= render('partials/page_hero', ['kicker' => 'Kabar desa', 'heading' => 'Berita & Kegiatan', 'text' => 'Informasi terbaru seputar kegiatan dan pengumuman desa.']) ?>

<section class="section section--lg">
    <div class="container">
        <?php if ($article): ?>
            <article class="card news-feature" id="artikel" style="margin-bottom:28px;border-color:var(--primary)">
                <img src="<?= media_url($article['image_id']) ?>" alt="<?= e($article['title']) ?>">
                <div class="news-feature__body">
                    <span class="label-up"><?= e($article['category']) ?></span>
                    <h2><?= e($article['title']) ?></h2>
                    <div class="small muted" style="margin-bottom:10px"><?= tgl($article['published_at']) ?> · <?= e($article['author'] ?: 'Admin Desa') ?></div>
                    <div class="prose small"><?= \App\paragraphs($article['content']) ?></div>
                    <a href="<?= e(qs(['baca' => null])) ?>" class="btn btn--ghost btn--sm btn--pill" style="align-self:flex-start"><?= icon('arrow-left', 'i-sm') ?> Kembali ke daftar</a>
                </div>
            </article>
        <?php endif; ?>

        <div class="filter-bar">
            <div class="pills" aria-label="Filter kategori">
                <a class="pill <?= $cat === '' ? 'is-active' : '' ?>" href="<?= e(qs(['kategori' => null, 'hal' => null, 'baca' => null])) ?>">Semua</a>
                <?php foreach ($cats as $c): ?>
                    <a class="pill <?= $cat === $c ? 'is-active' : '' ?>" href="<?= e(qs(['kategori' => $c, 'hal' => null, 'baca' => null])) ?>"><?= $c ?></a>
                <?php endforeach; ?>
            </div>
            <form method="get" class="search-mini" role="search">
                <?php if ($cat): ?><input type="hidden" name="kategori" value="<?= e($cat) ?>"><?php endif; ?>
                <label class="sr-only" for="cari-berita">Cari berita</label>
                <input class="input" type="search" id="cari-berita" name="cari" value="<?= e($search) ?>" placeholder="Cari berita...">
            </form>
        </div>

        <?php if ($showFeature): ?>
            <article class="card news-feature" style="margin-bottom:22px">
                <img src="<?= media_url($featured['image_id']) ?>" alt="<?= e($featured['title']) ?>">
                <div class="news-feature__body">
                    <span class="label-up">Berita utama</span>
                    <h2><?= e($featured['title']) ?></h2>
                    <p class="muted small"><?= e($featured['excerpt'] ?: excerpt($featured['content'], 160)) ?></p>
                    <p class="small muted"><?= tgl($featured['published_at'], true) ?></p>
                    <a href="?baca=<?= (int) $featured['id'] ?>" class="btn btn--pill" style="align-self:flex-start" data-news="<?= (int) $featured['id'] ?>">Baca selengkapnya <?= icon('arrow-right', 'i-sm') ?></a>
                </div>
            </article>
        <?php endif; ?>

        <?php if (!$items && !$showFeature): ?>
            <div class="card empty"><?= icon('newspaper') ?><p>Belum ada berita<?= $search !== '' ? ' untuk “' . e($search) . '”' : '' ?>.</p></div>
        <?php endif; ?>

        <div class="grid-3">
            <?php foreach ($items as $n): ?>
                <a class="card news-card" href="?baca=<?= (int) $n['id'] ?>" data-news="<?= (int) $n['id'] ?>">
                    <img src="<?= media_url($n['image_id']) ?>" alt="" loading="lazy">
                    <div class="news-card__body">
                        <span class="label-up"><?= e($n['category']) ?></span>
                        <h3><?= e($n['title']) ?></h3>
                        <p><?= e(excerpt($n['excerpt'] ?: $n['content'], 95)) ?></p>
                        <span class="news-card__date"><?= tgl($n['published_at'], true) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <?= render('partials/pagination', ['pg' => $pg, 'dots' => true]) ?>
    </div>
</section>
