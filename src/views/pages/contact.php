<?php

use function App\{e, icon, setting, render, csrf_field, old, social_links, brand_icon};

$wa = setting('contact_whatsapp');
?>
<?= render('partials/page_hero', ['kicker' => 'Hubungi kami', 'heading' => 'Kontak', 'text' => 'Sampaikan pertanyaan atau saran Anda kepada Pemerintah Desa.']) ?>

<section class="section section--lg">
    <div class="container split split--even" style="align-items:start">
        <div class="stack">
            <div class="card info-card"><span class="tile-icon tile-icon--soft"><?= icon('map-pin') ?></span><div><strong>Alamat</strong><p><?= e(setting('contact_address')) ?></p></div></div>
            <div class="card info-card"><span class="tile-icon tile-icon--soft"><?= icon('phone') ?></span><div><strong>Telepon</strong><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', setting('contact_phone'))) ?>"><?= e(setting('contact_phone')) ?></a>
                <?php if ($wa): ?><br><a href="https://wa.me/<?= e(preg_replace('/\D/', '', $wa)) ?>" target="_blank" rel="noopener"><?= icon('message-circle', 'i-sm') ?> Chat WhatsApp</a><?php endif; ?></div></div>
            <div class="card info-card"><span class="tile-icon tile-icon--soft"><?= icon('mail') ?></span><div><strong>Email</strong><a href="mailto:<?= e(setting('contact_email')) ?>"><?= e(setting('contact_email')) ?></a></div></div>
            <div class="card info-card"><span class="tile-icon tile-icon--soft"><?= icon('sun') ?></span><div><strong>Jam pelayanan</strong><p><?= e(setting('contact_hours_text')) ?></p></div></div>
            <div class="card info-card"><span class="tile-icon tile-icon--soft"><?= icon('share-2') ?></span><div style="flex:1"><strong>Media sosial</strong>
                <div class="socials" style="margin-top:8px">
                    <?php foreach (social_links() as $k => [$label, $url]): ?>
                        <a class="social social--<?= e($k) ?>" href="<?= e($url) ?>" target="_blank" rel="noopener"><?= brand_icon($k) ?> <?= e($label) ?></a>
                    <?php endforeach; ?>
                </div></div></div>
        </div>

        <form class="card" id="form-pesan" method="post" action="/kontak" novalidate style="padding:24px">
            <?= csrf_field() ?>
            <input class="hp-field" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
            <h2 style="margin-bottom:14px">Kirim pesan</h2>
            <div class="field"><label for="c-name">Nama</label><input class="input" id="c-name" name="name" required maxlength="100" value="<?= e(old('name')) ?>" placeholder="Nama lengkap"></div>
            <div class="field"><label for="c-email">Email</label><input class="input" type="email" id="c-email" name="email" maxlength="150" value="<?= e(old('email')) ?>" placeholder="nama@email.com"></div>
            <div class="field"><label for="c-phone">No. HP <span class="muted small">(opsional)</span></label><input class="input" id="c-phone" name="phone" inputmode="tel" maxlength="30" value="<?= e(old('phone')) ?>"></div>
            <div class="field"><label for="c-msg">Pesan</label><textarea class="textarea" id="c-msg" name="message" required maxlength="3000" placeholder="Tulis pesan Anda"><?= e(old('message')) ?></textarea></div>
            <button class="btn btn--block btn--pill" type="submit" style="min-height:48px">Kirim pesan</button>
            <p class="help center" style="margin-top:10px">Untuk pengaduan masalah di lapangan, gunakan <a href="/lapor">Lapor Desa</a>.</p>
        </form>
    </div>
</section>

<section class="section section--lg section--mint map-section">
    <div class="container">
        <div class="map-wrap" data-map-wrap>
            <div class="map" data-office-map data-lat="<?= e(setting('office_lat')) ?>" data-lng="<?= e(setting('office_lng')) ?>" data-title="Kantor Desa Marga Mulya" role="region" aria-label="Peta lokasi Kantor Desa"></div>
        </div>
        <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-top:12px;align-items:center">
            <strong><?= icon('map-pin', 'i-sm') ?> Kantor Desa Marga Mulya</strong>
            <a class="btn btn--pill btn--sm" target="_blank" rel="noopener" href="https://www.google.com/maps/dir/?api=1&destination=<?= e(setting('office_lat')) ?>,<?= e(setting('office_lng')) ?>"><?= icon('navigation', 'i-sm') ?> Petunjuk arah</a>
        </div>
    </div>
</section>
