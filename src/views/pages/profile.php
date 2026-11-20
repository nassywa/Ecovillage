<?php

use function App\{e, icon, setting, media_url, angka, paragraphs, render, office_hours};

$tiers = [1 => [], 2 => [], 3 => [], 4 => []];
foreach ($officials as $o) {
    $tiers[(int) $o['tier']][] = $o;
}
$landTotal = array_sum(array_map(fn ($r) => (float) $r['value'], $land));
$palette = ['#5B8C2A', '#4CB848', '#2E8B3A', '#1F6E43', '#2B8A6E', '#3FB6A8', '#167A6B', '#2AA7C9', '#64748B'];
$initials = fn ($n) => mb_strtoupper(implode('', array_map(fn ($w) => mb_substr($w, 0, 1), array_slice(explode(' ', $n), 0, 2))));
?>
<?= render('partials/page_hero', ['kicker' => setting('village_name'), 'heading' => 'Profil Desa', 'text' => 'Kenali lebih dekat desa kami: visi, warga, dan wilayahnya.']) ?>

<section class="section section--lg section--mint">
    <div class="container">
        <h2 style="font-size:clamp(1.6rem,1.2rem + 1.4vw,2.2rem);margin-bottom:22px">Visi &amp; Misi</h2>
        <div class="grid-2">
            <div class="card vm-card" style="padding:24px">
                <span class="tile-icon"><?= icon('eye') ?></span>
                <div class="label-up" style="margin-top:14px">Visi</div>
                <h3><?= e(setting('profil_visi')) ?></h3>
            </div>
            <div class="card vm-card" style="padding:24px">
                <span class="tile-icon"><?= icon('target') ?></span>
                <div class="label-up" style="margin-top:14px">Misi</div>
                <h3><?= e(setting('profil_misi_ringkas', $missions[0] ?? '')) ?></h3>
                <?php if ($missions): ?>
                    <details style="margin-top:10px"><summary class="link-more" style="cursor:pointer">Lihat rincian misi</summary>
                        <ol class="small muted" style="margin:8px 0 0;padding-left:18px"><?php foreach ($missions as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ol>
                    </details>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="section section--lg" id="demografi">
    <div class="container">
        <span class="kicker">Warga kami</span>
        <h2 style="font-size:clamp(1.6rem,1.2rem + 1.4vw,2.2rem);margin-bottom:22px">Data Demografi</h2>
        <div class="grid-3">
            <div class="card card--mint demo-card"><span class="tile-icon tile-icon--white"><?= icon('users') ?></span><div class="demo-card__num"><?= angka(setting('stat_penduduk')) ?></div><div class="demo-card__label">Penduduk</div><div class="demo-card__unit">jiwa</div></div>
            <div class="card card--mint demo-card"><span class="tile-icon tile-icon--white"><?= icon('house') ?></span><div class="demo-card__num"><?= angka(setting('stat_kk')) ?></div><div class="demo-card__label">Kepala Keluarga</div><div class="demo-card__unit">KK</div></div>
            <div class="card card--mint demo-card"><span class="tile-icon tile-icon--white"><?= icon('user') ?></span><div class="demo-card__num"><?= angka(setting('stat_rt')) ?></div><div class="demo-card__label">RT</div><div class="demo-card__unit">rukun tetangga · <?= angka(setting('stat_rw')) ?> RW</div></div>
        </div>
        <p style="margin-top:16px"><a class="link-more" href="/informasi?bagian=B">Lihat data kependudukan lengkap →</a></p>
    </div>
</section>

<section class="section section--lg section--mint" id="wilayah">
    <div class="container">
        <div class="section-head--split">
            <div><span class="kicker">Wilayah</span><h2>Kondisi Geografis</h2></div>
            <p>Pilih tampilan peta untuk melihat batas wilayah desa, RW, atau RT. Klik poligon untuk detail.</p>
        </div>
        <div class="segmented" role="tablist" aria-label="Pilih peta" style="margin-bottom:14px">
            <button class="is-active" type="button" data-map-level="desa" role="tab" aria-selected="true">Peta Wilayah Desa</button>
            <button type="button" data-map-level="rw" role="tab" aria-selected="false">Peta Wilayah RW</button>
            <button type="button" data-map-level="rt" role="tab" aria-selected="false">Peta Wilayah RT</button>
        </div>
        <div class="map-wrap" data-map-wrap>
            <div class="map" id="village-map" data-village-map data-office="<?= e(setting('office_lat')) ?>,<?= e(setting('office_lng')) ?>" role="region" aria-label="Peta wilayah Desa Marga Mulya"></div>
            <div class="map-tools">
                <button type="button" data-map-zoom="in" title="Perbesar" aria-label="Perbesar"><?= icon('zoom-in') ?></button>
                <button type="button" data-map-zoom="out" title="Perkecil" aria-label="Perkecil"><?= icon('zoom-out') ?></button>
                <button type="button" data-map-full title="Layar penuh" aria-label="Layar penuh"><?= icon('maximize') ?></button>
                <button type="button" data-map-office title="Cari Kantor Desa" aria-label="Cari Kantor Desa"><?= icon('landmark') ?></button>
                <button type="button" data-map-locate title="Lokasi saya saat ini" aria-label="Lokasi saya saat ini"><?= icon('locate-fixed') ?></button>
            </div>
        </div>
        <div class="map-legend" data-map-legend></div>
        <p class="help" style="margin-top:6px">Batas wilayah pada peta merupakan ilustrasi untuk keperluan informasi, bukan batas resmi.</p>

        <div class="split split--even" style="margin-top:18px;grid-template-columns:1fr 1.25fr">
            <div class="card" style="padding:22px">
                <div class="label-up">Luas wilayah</div>
                <div style="display:flex;align-items:baseline;gap:10px;margin-top:6px"><span class="big-num"><?= angka(setting('geo_luas')) ?></span><span class="muted">hektare</span></div>
                <div class="small muted">≈ <?= angka((float) setting('geo_luas') / 100, 1) ?> km²</div>
                <dl class="kv small" style="margin-top:16px">
                    <dt>Ketinggian</dt><dd><?= e(setting('geo_ketinggian')) ?></dd>
                    <dt>Iklim</dt><dd><?= e(setting('geo_iklim')) ?></dd>
                    <dt>Jarak</dt><dd><?= e(setting('geo_jarak')) ?></dd>
                </dl>
            </div>
            <div class="card" style="padding:22px">
                <div class="label-up">Batas wilayah</div>
                <div class="border-tiles">
                    <div class="border-tile"><small>↑ Utara</small><?= e(setting('geo_utara')) ?></div>
                    <div class="border-tile"><small>→ Timur</small><?= e(setting('geo_timur')) ?></div>
                    <div class="border-tile"><small>↓ Selatan</small><?= e(setting('geo_selatan')) ?></div>
                    <div class="border-tile"><small>← Barat</small><?= e(setting('geo_barat')) ?></div>
                </div>
            </div>
        </div>

        <div class="card" style="padding:24px;margin-top:18px">
            <h3>Luas Wilayah menurut Penggunaan</h3>
            <div class="split split--even" style="align-items:center;grid-template-columns:.8fr 1.2fr">
                <div class="chart-box chart-box--round" style="height:260px"><canvas data-chart='<?= e(json_encode([
                    'type' => 'doughnut', 'unit' => $landUnit, 'legend' => false, 'colors' => $palette,
                    'labels' => array_column($land, 'label'),
                    'datasets' => [['label' => 'Luas', 'data' => array_map('floatval', array_column($land, 'value'))]],
                ])) ?>' role="img" aria-label="Diagram penggunaan lahan"></canvas></div>
                <table class="land-table">
                    <thead><tr><th>Penggunaan</th><th class="num">Luas (ha)</th><th class="num">%</th></tr></thead>
                    <tbody>
                    <?php foreach ($land as $i => $l): $pct = $landTotal ? $l['value'] / $landTotal * 100 : 0; ?>
                        <tr><td><span class="land-dot" style="background:<?= $palette[$i % count($palette)] ?>"></span><?= e($l['label']) ?></td><td class="num"><?= angka($l['value']) ?></td><td class="num"><?= angka($pct, 1) ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<section class="section section--lg">
    <div class="container split" style="grid-template-columns:1fr 1fr;gap:28px">
        <div>
            <span class="kicker">Tentang desa</span>
            <h2 style="font-size:clamp(1.6rem,1.2rem + 1.4vw,2.2rem)">Sejarah Desa Marga Mulya</h2>
            <p class="muted"><?= e(setting('profil_tentang')) ?></p>
            <div class="prose"><?= paragraphs(setting('profil_sejarah')) ?></div>
        </div>
        <div>
            <img class="cover" src="<?= media_url(setting('profile_image')) ?>" alt="Gerbang Desa Marga Mulya" loading="lazy" style="aspect-ratio:4/3">
        </div>
    </div>
</section>

<section class="section section--lg section--mint" id="pemerintahan">
    <div class="container">
        <span class="kicker">Pemerintahan desa</span>
        <h2 style="font-size:clamp(1.6rem,1.2rem + 1.4vw,2.2rem);margin-bottom:22px">Struktur &amp; Perangkat Desa</h2>
        <div class="card" style="overflow-x:auto;padding:24px">
            <div class="org">
                <?php foreach ($tiers as $t => $list): if (!$list) continue; ?>
                    <div class="org__level">
                        <?php foreach ($list as $o): ?>
                            <div class="org__node <?= $t === 1 ? 'org__node--head' : '' ?>">
                                <?php if ($o['photo_id']): ?><img class="avatar" style="width:44px;height:44px;margin-bottom:4px" src="<?= media_url($o['photo_id']) ?>" alt=""><?php endif; ?>
                                <strong><?= e($o['position']) ?></strong><span><?= e($o['name']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<section class="section section--lg" id="pelayanan">
    <div class="container split" style="grid-template-columns:1fr 1fr;gap:28px;align-items:center">
        <img class="cover" src="<?= media_url(setting('office_image')) ?>" alt="Kantor Desa Marga Mulya" loading="lazy" style="aspect-ratio:16/10">
        <div>
            <span class="kicker">Pelayanan</span>
            <h2 style="font-size:clamp(1.6rem,1.2rem + 1.4vw,2.2rem)">Kantor Desa Marga Mulya</h2>
            <p class="muted"><?= e(setting('contact_hours_text')) ?> Indeks kepuasan pelayanan warga: <strong><?= angka(setting('stat_pelayanan')) ?>%</strong>.</p>
            <div class="card" style="margin-top:14px"><?= render('partials/hours', ['hours' => office_hours(), 'hoursTitle' => 'Jam Pelayanan']) ?></div>
            <div class="row-actions" style="justify-content:flex-start;margin-top:14px;display:flex;gap:10px;flex-wrap:wrap">
                <a class="btn btn--pill" href="/lapor"><?= icon('megaphone', 'i-sm') ?> Lapor Desa</a>
                <a class="btn btn--ghost btn--pill" href="/kontak">Hubungi Kami</a>
            </div>
        </div>
    </div>
</section>
