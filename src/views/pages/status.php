<?php

use function App\{e, icon, tgl, render, qs, report_status_class, angka, media_url};

$steps = ['Diterima', 'Diproses', 'Selesai'];
?>
<?= render('partials/page_hero', ['kicker' => 'Lapor Desa', 'heading' => 'Cek Status Laporan', 'text' => 'Cari dengan nomor tiket, atau lihat seluruh laporan warga berdasarkan kategori dan status.']) ?>

<section class="section section--lg">
    <div class="container">
        <form method="get" action="/cek-status" class="card" role="search">
            <label class="label" for="tiket">Nomor tiket</label>
            <div class="input-group" style="margin-top:6px">
                <input class="input" id="tiket" name="tiket" value="<?= e($ticket) ?>" placeholder="Contoh: LD-2611-1C7E6" autocomplete="off">
                <button class="btn" type="submit"><?= icon('search') ?> Cari</button>
            </div>
            <?php if ($cat): ?><input type="hidden" name="kategori" value="<?= e($cat) ?>"><?php endif; ?>
        </form>

        <?php if ($ticket !== '' && $detail): ?>
            <?php
            $st = $detail['status'];
            $idx = array_search($st, $steps, true);
            $fill = $st === 'Ditolak' ? 0 : ($idx === false ? 0 : $idx / 2 * 76);
            ?>
            <article class="card" style="margin-top:16px;border-color:var(--primary)" aria-live="polite">
                <div class="report-card__head">
                    <span class="report-card__ticket">Laporan #<?= e($detail['ticket']) ?></span>
                    <span class="badge <?= report_status_class($st) ?>"><?= e($st) ?></span>
                </div>
                <div class="split split--even" style="margin-top:12px">
                    <div>
                        <dl class="kv">
                            <dt>Kategori</dt><dd><?= e($detail['category']) ?></dd>
                            <dt>Lokasi</dt><dd><?= e($detail['location']) ?></dd>
                            <dt>Tanggal kirim</dt><dd><?= tgl($detail['created_at'], false, true) ?></dd>
                            <dt>Tanggal diterima</dt><dd><?= $detail['accepted_at'] ? tgl($detail['accepted_at'], false, true) : '-' ?></dd>
                            <?php if ($detail['finished_at']): ?><dt>Tanggal selesai</dt><dd><?= tgl($detail['finished_at'], false, true) ?></dd><?php endif; ?>
                        </dl>
                        <p style="margin-top:12px" class="small"><?= nl2br(e($detail['description'])) ?></p>
                        <?php if ($detail['admin_note']): ?>
                            <div class="alert alert--info"><?= icon('message-square') ?><span><strong>Catatan petugas:</strong> <?= e($detail['admin_note']) ?></span></div>
                        <?php endif; ?>

                        <?php if ($st === 'Ditolak'): ?>
                            <div class="alert alert--error"><?= icon('circle-x') ?><span>Laporan ini ditolak atau dialihkan. Lihat catatan petugas.</span></div>
                        <?php else: ?>
                            <div class="stepper" aria-label="Progres laporan">
                                <div class="stepper__fill" style="width:<?= $fill ?>%"></div>
                                <?php foreach ($steps as $i => $s): $done = $idx !== false && $i <= $idx; ?>
                                    <div class="step <?= $done ? 'is-done' : '' ?>">
                                        <span class="step__dot"><?= $done ? icon('check', 'i-sm') : ($i + 1) ?></span><?= $s ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <?php if ($detail['photo_id']): ?>
                            <img class="cover" style="aspect-ratio:4/3;margin-bottom:14px" src="<?= media_url($detail['photo_id']) ?>" alt="Foto laporan" data-lightbox data-caption="Foto laporan #<?= e($detail['ticket']) ?>">
                        <?php endif; ?>
                        <h3>Riwayat</h3>
                        <ol class="timeline">
                            <?php foreach ($history as $h): ?>
                                <li><strong><?= e($h['status']) ?></strong><div class="timeline__time"><?= tgl($h['created_at'], true, true) ?></div><?php if ($h['note']): ?><div class="small"><?= e($h['note']) ?></div><?php endif; ?></li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </div>
            </article>
        <?php elseif ($ticket !== '' && !$rows): ?>
            <div class="alert alert--error" style="margin-top:16px"><?= icon('circle-alert') ?><span>Nomor tiket <strong><?= e($ticket) ?></strong> tidak ditemukan. Periksa kembali penulisannya.</span></div>
        <?php endif; ?>

        <?php if ($mine): ?>
            <div class="card" style="margin-top:16px">
                <h3>Laporan saya</h3>
                <div class="pills">
                    <?php foreach ($mine as $m): ?>
                        <a class="pill" href="/cek-status?tiket=<?= e($m['ticket']) ?>"><?= e($m['ticket']) ?> · <?= e($m['status']) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid-stats" style="grid-template-columns:repeat(auto-fit,minmax(110px,1fr));margin-top:20px">
            <?php foreach (\App\REPORT_STATUSES as $s): ?>
                <a class="card stat" href="<?= e(qs(['status' => $status === $s ? null : $s, 'hal' => null, 'tiket' => null])) ?>" style="<?= $status === $s ? 'border-color:var(--primary)' : '' ?>">
                    <span class="stat__num"><?= angka($summary[$s] ?? 0) ?></span><span class="badge <?= report_status_class($s) ?>"><?= $s ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <h2 style="margin-top:24px">Daftar Laporan Warga</h2>
        <div class="pills" style="margin-bottom:14px" aria-label="Filter kategori">
            <a class="pill <?= $cat === '' ? 'is-active' : '' ?>" href="<?= e(qs(['kategori' => null, 'hal' => null, 'tiket' => null])) ?>">Semua</a>
            <?php foreach ($categories as $c): ?>
                <a class="pill <?= $cat === $c['name'] ? 'is-active' : '' ?>" href="<?= e(qs(['kategori' => $c['name'], 'hal' => null, 'tiket' => null])) ?>"><?= icon($c['icon'], 'i-sm') ?> <?= e($c['name']) ?></a>
            <?php endforeach; ?>
        </div>
        <?php if ($status): ?><p class="small muted">Menampilkan status <strong><?= e($status) ?></strong> · <a href="<?= e(qs(['status' => null])) ?>">tampilkan semua status</a></p><?php endif; ?>

        <?php if (!$rows): ?>
            <div class="card empty"><?= icon('inbox') ?><p>Tidak ada laporan yang cocok.</p></div>
        <?php else: ?>
            <div class="grid-2">
                <?php foreach ($rows as $r): ?>
                    <a class="card report-card" href="/cek-status?tiket=<?= e($r['ticket']) ?>" style="color:inherit">
                        <div class="report-card__head">
                            <span class="report-card__ticket">Laporan #<?= e($r['ticket']) ?></span>
                            <span class="badge <?= report_status_class($r['status']) ?>"><?= e($r['status']) ?></span>
                        </div>
                        <dl class="kv">
                            <dt>Kategori</dt><dd><?= e($r['category']) ?></dd>
                            <dt>Lokasi</dt><dd><?= e($r['location']) ?></dd>
                            <dt>Tanggal kirim</dt><dd><?= tgl($r['created_at'], true) ?></dd>
                            <dt>Tanggal diterima</dt><dd><?= $r['accepted_at'] ? tgl($r['accepted_at'], true) : '-' ?></dd>
                        </dl>
                    </a>
                <?php endforeach; ?>
            </div>
            <?= render('partials/pagination', ['pg' => $pg, 'dots' => true]) ?>
        <?php endif; ?>

        <div class="card" style="margin-top:22px;display:flex;gap:14px;align-items:center;justify-content:space-between;flex-wrap:wrap">
            <div><strong>Punya masalah lain di sekitar Anda?</strong><div class="muted small">Laporkan sekarang, petugas desa akan menindaklanjuti.</div></div>
            <a href="/lapor" class="btn btn--lapor"><?= icon('megaphone') ?> Buat Laporan</a>
        </div>
    </div>
</section>
