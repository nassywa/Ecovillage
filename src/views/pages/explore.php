<?php

use function App\{e, icon, setting, media_url, render, excerpt, qs};

$catIcons = ['UMKM' => 'store', 'Wisata' => 'mountain', 'Budaya' => 'drama'];
$total = array_sum($counts);
$spotClass = ['spot--tall', '', '', 'spot--wide'];
$filtered = $cat !== '' || $search !== '' || $pg['page'] > 1;
?>
<?= render('partials/page_hero', ['kicker' => 'Jelajah desa', 'heading' => 'Sudut Istimewa', 'text' => 'Tempat dan potensi terbaik Desa Marga Mulya.']) ?>

<?php if ($spots && !$filtered): ?>
<section class="section section--lg">
    <div class="container">
        <div class="section-head--split">
            <div><span class="kicker">Lokasi pilihan</span><h2>Sudut Favorit Warga</h2></div>
            <p><?= e(setting('jelajah_intro')) ?></p>
        </div>
        <div class="spot-grid">
            <?php foreach ($spots as $i => $s): ?>
                <button type="button" class="spot <?= $spotClass[$i] ?? '' ?>" data-place="<?= (int) $s['id'] ?>" aria-label="Lihat detail <?= e($s['name']) ?>">
                    <img src="<?= media_url($s['image_id']) ?>" alt="" loading="lazy">
                    <span class="spot__body"><strong><?= e($s['name']) ?></strong><span><?= e(excerpt($s['description'], 48)) ?></span></span>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($products && !$filtered): ?>
<section class="section section--lg section--mint">
    <div class="container">
        <div class="section-head--split">
            <div><span class="kicker">Potensi lokal</span><h2>Produk Unggulan</h2></div>
            <a class="btn btn--pill" href="/jelajah?kategori=UMKM#direktori">Direktori UMKM <?= icon('arrow-right', 'i-sm') ?></a>
        </div>
        <div class="grid-3">
            <?php foreach ($products as $p): ?>
                <article class="card product-card">
                    <img src="<?= media_url($p['image_id']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                    <div class="product-card__body">
                        <h3 style="font-size:1rem;margin:0"><?= e($p['name']) ?></h3>
                        <div class="product-card__price"><?= e($p['price']) ?></div>
                        <button class="btn btn--sm btn--pill" type="button" data-place="<?= (int) $p['id'] ?>">Lihat detail</button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section section--lg" id="direktori" style="scroll-margin-top:80px">
    <div class="container">
        <div class="section-head--split">
            <div><span class="kicker">Direktori</span><h2>UMKM, Wisata &amp; Budaya</h2></div>
            <p>Cari tempat atau barang, lalu saring berdasarkan kategori.</p>
        </div>
        <div class="filter-bar">
            <div class="pills" aria-label="Filter kategori">
                <a class="pill <?= $cat === '' ? 'is-active' : '' ?>" href="<?= e(qs(['kategori' => null, 'hal' => null])) ?>#direktori">Semua <span class="pill__count"><?= $total ?></span></a>
                <?php foreach (['UMKM', 'Wisata', 'Budaya'] as $c): ?>
                    <a class="pill <?= $cat === $c ? 'is-active' : '' ?>" href="<?= e(qs(['kategori' => $c, 'hal' => null])) ?>#direktori"><?= icon($catIcons[$c], 'i-sm') ?> <?= $c ?> <span class="pill__count"><?= (int) ($counts[$c] ?? 0) ?></span></a>
                <?php endforeach; ?>
            </div>
            <form method="get" action="/jelajah#direktori" class="search-mini" role="search">
                <?php if ($cat): ?><input type="hidden" name="kategori" value="<?= e($cat) ?>"><?php endif; ?>
                <label class="sr-only" for="cari">Cari tempat atau barang</label>
                <input class="input" type="search" id="cari" name="cari" value="<?= e($search) ?>" placeholder="Cari tempat atau barang...">
            </form>
        </div>

        <?php if ($search !== ''): ?>
            <p class="muted small"><?= $pg['total'] ?> hasil untuk “<?= e($search) ?>” · <a href="<?= e(qs(['cari' => null, 'hal' => null])) ?>#direktori">Hapus pencarian</a></p>
        <?php endif; ?>

        <?php if (!$items): ?>
            <div class="card empty"><?= icon('search-x') ?><p>Tidak ada hasil yang cocok. Coba kata kunci lain.</p></div>
        <?php else: ?>
            <div class="grid-3">
                <?php foreach ($items as $p): ?>
                    <article class="card product-card">
                        <img src="<?= media_url($p['image_id']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                        <div class="product-card__body">
                            <span class="label-up"><?= e($p['category']) ?> · <?= e($p['subcategory']) ?></span>
                            <h3 style="font-size:1rem;margin:4px 0 2px"><?= e($p['name']) ?></h3>
                            <div class="small muted"><?= icon('map-pin', 'i-sm') ?> <?= e($p['location'] ?: '-') ?></div>
                            <div class="product-card__price" style="font-size:.95rem;margin-top:6px"><?= e($p['price'] ?: '') ?></div>
                            <button class="btn btn--sm btn--pill" type="button" data-place="<?= (int) $p['id'] ?>">Lihat detail</button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <?= render('partials/pagination', ['pg' => $pg, 'dots' => true]) ?>
        <?php endif; ?>
    </div>
</section>
