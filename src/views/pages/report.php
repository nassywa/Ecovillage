<?php

use function App\{e, icon, setting, csrf_field, old, user, render};

$u = user();
?>
<?= render('partials/page_hero', ['kicker' => setting('village_name'), 'heading' => 'Lapor Desa', 'text' => setting('lapor_intro')]) ?>

<section class="section section--lg">
    <div class="container split split--form">
        <div>
            <?php if ($done): ?>
                <div class="card ticket-box" id="berhasil" style="margin-bottom:18px;padding:26px">
                    <?= icon('circle-check', 'i-lg') ?>
                    <h2 style="margin-top:6px">Laporan berhasil dikirim!</h2>
                    <p class="muted mb-0">Simpan nomor tiket berikut untuk memantau status laporan Anda.</p>
                    <div class="ticket-box__code"><?= e($done) ?></div>
                    <button class="btn btn--soft btn--pill" type="button" data-copy="<?= e($done) ?>"><?= icon('copy') ?> Salin nomor tiket</button>
                    <a class="btn btn--pill" href="/cek-status?tiket=<?= e($done) ?>"><?= icon('search-check') ?> Cek status</a>
                </div>
            <?php endif; ?>

            <form class="card" id="form-lapor" style="padding:24px" method="post" action="/lapor" enctype="multipart/form-data" data-upload-form data-report-form novalidate>
                <?php if (!$u): ?>
                    <div class="notice">
                        <?= icon('lock') ?>
                        <div>
                            <strong>Masuk untuk mengirim laporan</strong>
                            <p>Pengiriman laporan memerlukan akun pengguna. Anda tetap dapat <a href="/cek-status">mengecek status laporan</a> tanpa masuk.</p>
                            <a href="/masuk?kembali=/lapor" class="btn btn--sm btn--pill">Masuk atau Daftar <?= icon('arrow-right', 'i-sm') ?></a>
                        </div>
                    </div>
                <?php endif; ?>

                <ol class="steps" aria-label="Langkah pengisian">
                    <li data-step="category"><span class="steps__dot">1</span>Kategori</li>
                    <li data-step="location"><span class="steps__dot">2</span>Lokasi</li>
                    <li data-step="description"><span class="steps__dot">3</span>Uraian</li>
                    <li data-step="photo"><span class="steps__dot">4</span>Foto</li>
                </ol>

                <fieldset <?= $u ? '' : 'disabled' ?>>
                    <?= csrf_field() ?>
                    <input class="hp-field" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">

                    <div class="field">
                        <label for="category">Kategori masalah <span class="req">*</span></label>
                        <select class="select" id="category" name="category" required>
                            <option value="">Pilih kategori masalah</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= e($c['name']) ?>" <?= old('category') === $c['name'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid-2" style="gap:0 14px">
                        <div class="field">
                            <label for="location">Lokasi kejadian <span class="req">*</span></label>
                            <input class="input" id="location" name="location" required maxlength="160" value="<?= e(old('location')) ?>" placeholder="Lokasi kejadian (jalan, RT/RW, patokan)">
                        </div>
                        <div class="field">
                            <label for="rt">RT / RW</label>
                            <select class="select" id="rt" name="rt">
                                <option value="">Pilih RT (opsional)</option>
                                <?php foreach ($rts as $r): $v = $r['name'] . ' / ' . $r['parent_name']; ?>
                                    <option value="<?= e($v) ?>" <?= old('rt') === $v ? 'selected' : '' ?>><?= e($v) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="field">
                        <span class="label">Titik lokasi di peta</span>
                        <span class="help">Opsional: klik peta untuk menandai titik kejadian.</span>
                        <div class="map-pick" data-report-map data-center='<?= e(json_encode([(float) setting('map_lat', '-2.47'), (float) setting('map_lng', '111.92')])) ?>'></div>
                        <input type="hidden" name="lat" value="<?= e(old('lat')) ?>">
                        <input type="hidden" name="lng" value="<?= e(old('lng')) ?>">
                    </div>

                    <div class="field">
                        <label for="description">Deskripsi masalah <span class="req">*</span></label>
                        <textarea class="textarea" id="description" name="description" required minlength="20" maxlength="1500" placeholder="Jelaskan masalah yang ditemukan, sejak kapan, dan dampaknya bagi warga" data-counter><?= e(old('description')) ?></textarea>
                        <span class="help" data-counter-out>0/1500 karakter · minimal 20</span>
                    </div>

                    <div class="field">
                        <span class="label">Foto pendukung <span class="req">*</span></span>
                        <label class="upload dropzone" data-dropzone>
                            <?= icon('image', 'i-lg') ?>
                            <strong>Tarik foto pendukung ke sini atau pilih berkas</strong>
                            <span class="upload__name">Wajib · JPG, PNG, atau WebP · maksimal 3 MB</span>
                            <img class="upload__preview" alt="Pratinjau foto">
                            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" data-resize required>
                        </label>
                    </div>

                    <div class="grid-2" style="gap:0 14px">
                        <div class="field">
                            <label for="reporter_name">Nama pelapor</label>
                            <input class="input" id="reporter_name" name="reporter_name" maxlength="100" value="<?= e(old('reporter_name', $u['name'] ?? '')) ?>">
                        </div>
                        <div class="field">
                            <label for="reporter_phone">No. HP / WhatsApp</label>
                            <input class="input" id="reporter_phone" name="reporter_phone" inputmode="tel" maxlength="20" value="<?= e(old('reporter_phone', $u['phone'] ?? '')) ?>" placeholder="Untuk dihubungi petugas">
                        </div>
                    </div>
                    <p class="help"><?= icon('shield-check', 'i-sm') ?> Nama dan nomor HP tidak ditampilkan ke publik.</p>

                    <button class="btn btn--block btn--pill" type="submit" style="min-height:48px">Kirim Laporan</button>
                </fieldset>
            </form>
        </div>

        <aside class="stack sticky-side">
            <div class="card card--mint" style="padding:22px">
                <h3>Panduan singkat</h3>
                <ul class="guide">
                    <li><?= icon('circle-check') ?> Gunakan bahasa yang santun dan jelas.</li>
                    <li><?= icon('circle-check') ?> Sebutkan lokasi sedetail mungkin.</li>
                    <li><?= icon('circle-check') ?> Lampirkan foto pendukung laporan.</li>
                    <li><?= icon('circle-check') ?> Pantau status lewat nomor tiket.</li>
                </ul>
                <a href="/cek-status" class="btn btn--light btn--block btn--pill" style="border:1px solid var(--border)"><?= icon('file-search', 'i-sm') ?> Cek Status Laporan</a>
            </div>
            <div class="card" style="padding:22px">
                <h3>Cek cepat nomor tiket</h3>
                <form method="get" action="/cek-status">
                    <label class="sr-only" for="tiket">Nomor tiket</label>
                    <div class="input-group" style="margin-top:6px">
                        <input class="input" id="tiket" name="tiket" placeholder="LD-2611-1C7E6" required>
                        <button class="btn" type="submit">Cari</button>
                    </div>
                </form>
            </div>
        </aside>
    </div>
</section>
