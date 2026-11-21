<?php

use function App\{e, icon, setting, media_url, tgl, render, qs, user, csrf_field};

$u = user();
$cats = ['Kegiatan', 'Wisata', 'UMKM', 'Pemerintahan'];
?>
<?= render('partials/page_hero', ['kicker' => 'Dokumentasi desa', 'heading' => 'Galeri', 'text' => setting('gallery_intro')]) ?>

<section class="section section--lg">
    <div class="container">
        <div class="filter-bar">
            <div class="pills" aria-label="Filter kategori">
                <a class="pill <?= $cat === '' ? 'is-active' : '' ?>" href="/galeri">Semua</a>
                <?php foreach ($cats as $c): ?>
                    <a class="pill <?= $cat === $c ? 'is-active' : '' ?>" href="/galeri?kategori=<?= rawurlencode($c) ?>"><?= e($c) ?></a>
                <?php endforeach; ?>
            </div>
            <a href="#kirim-foto" class="btn btn--sm btn--pill btn--soft"><?= icon('upload', 'i-sm') ?> Kirim foto</a>
        </div>

        <?php if (!$items): ?>
            <div class="card empty"><?= icon('images') ?><p>Belum ada foto pada kategori ini.</p></div>
        <?php else: ?>
            <div class="masonry" data-load-grid>
                <?php foreach ($items as $g): ?>
                    <figure class="masonry__item" style="margin:0 0 18px">
                        <img src="<?= media_url($g['image_id']) ?>" alt="<?= e($g['title']) ?>" loading="lazy" data-lightbox data-caption="<?= e($g['title'] . ($g['description'] ? ' — ' . $g['description'] : '')) ?>">
                        <figcaption>
                            <div class="masonry__title"><?= e($g['title']) ?></div>
                            <div class="masonry__meta"><?= e($g['category'] ?: 'Umum') ?><?= $g['user_id'] ? ' · kiriman ' . e($g['uploader_name']) : '' ?></div>
                        </figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
            <?php if ($pg['page'] < $pg['pages']): ?>
                <div class="center" data-load-wrap style="margin-top:10px">
                    <a class="btn btn--soft btn--pill" style="min-width:200px" href="<?= e(qs(['hal' => $pg['page'] + 1])) ?>" data-load-more>Muat lebih banyak</a>
                </div>
            <?php endif; ?>
            <noscript><?= render('partials/pagination', ['pg' => $pg]) ?></noscript>
        <?php endif; ?>
    </div>
</section>

<section class="section section--lg section--mint" id="kirim-foto">
    <div class="container split split--even">
        <div>
            <span class="kicker">Partisipasi warga</span>
            <h2 style="font-size:clamp(1.6rem,1.2rem + 1.4vw,2.2rem)">Bagikan Foto Desa Anda</h2>
            <p class="muted">Warga dapat mengirim foto kegiatan atau keindahan desa. Foto akan tampil setelah diperiksa admin desa. Ukuran foto otomatis diperkecil agar cepat diunggah.</p>
            <?php if ($mine): ?>
                <div class="card" style="margin-top:16px">
                    <h3>Foto yang Anda kirim</h3>
                    <div class="table-wrap">
                        <table class="table">
                            <thead><tr><th>Judul</th><th>Tanggal</th><th>Status</th></tr></thead>
                            <tbody>
                            <?php foreach ($mine as $m): ?>
                                <tr><td><?= e($m['title']) ?></td><td><?= tgl($m['created_at'], true) ?></td>
                                    <td><span class="badge <?= $m['status'] === 'Disetujui' ? 'badge--ok' : ($m['status'] === 'Ditolak' ? 'badge--danger' : 'badge--warn') ?>"><?= e($m['status']) ?></span></td></tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="card" style="padding:22px">
            <?php if (!$u): ?>
                <div class="notice" style="margin:0 0 4px"><?= icon('lock') ?><div><strong>Masuk untuk mengirim foto</strong><p>Pengiriman foto memerlukan akun warga.</p>
                    <a href="/masuk?kembali=/galeri%23kirim-foto" class="btn btn--sm btn--pill">Masuk atau Daftar <?= icon('arrow-right', 'i-sm') ?></a></div></div>
            <?php else: ?>
                <form method="post" action="/galeri" enctype="multipart/form-data" data-upload-form>
                    <?= csrf_field() ?>
                    <div class="field">
                        <label for="g-title">Judul foto <span class="req">*</span></label>
                        <input class="input" id="g-title" name="title" required maxlength="150" placeholder="Contoh: Panen jagung di RT 05">
                    </div>
                    <div class="field">
                        <label for="g-cat">Kategori</label>
                        <select class="select" id="g-cat" name="category">
                            <?php foreach ($cats as $c): ?><option><?= $c ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="g-desc">Keterangan</label>
                        <textarea class="textarea" id="g-desc" name="description" maxlength="300" style="min-height:80px" placeholder="Ceritakan singkat tentang foto ini"></textarea>
                    </div>
                    <div class="field">
                        <span class="label">Foto <span class="req">*</span></span>
                        <label class="upload dropzone" data-dropzone>
                            <?= icon('image', 'i-lg') ?>
                            <strong>Tarik foto ke sini atau pilih berkas</strong>
                            <span class="upload__name">JPG, PNG, atau WEBP</span>
                            <img class="upload__preview" alt="Pratinjau foto">
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" required data-resize>
                        </label>
                    </div>
                    <button class="btn btn--block btn--pill" type="submit"><?= icon('send') ?> Kirim Foto</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>
