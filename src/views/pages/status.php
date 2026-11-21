<?php

use function App\{e, icon, tgl, render, report_status_class};

$steps = ['Diterima', 'Diproses', 'Selesai'];
?>
<?= render('partials/page_hero', ['kicker' => 'Lapor Desa', 'heading' => 'Cek Status Laporan', 'text' => 'Cari riwayat perubahan status menggunakan nomor tiket laporan.']) ?>

<section class="section section--lg">
    <div class="container">
        <form method="get" action="/cek-status" class="card" role="search">
            <label class="label" for="ticket_number">Nomor tiket</label>
            <div class="input-group" style="margin-top:6px">
                <input class="input" id="ticket_number" name="ticket_number" value="<?= e($ticket) ?>" placeholder="Contoh: LD-2611-1C7E6" autocomplete="off">
                <button class="btn" type="submit"><?= icon('search') ?> Cari</button>
            </div>
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
                        <h3>Riwayat</h3>
                        <ol class="timeline">
                            <?php foreach ($history as $h): ?>
                                <li><strong><?= e($h['status']) ?></strong><div class="timeline__time"><?= tgl($h['created_at'], true, true) ?></div><?php if ($h['note']): ?><div class="small"><?= e($h['note']) ?></div><?php endif; ?></li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </div>
            </article>
        <?php elseif ($ticket !== ''): ?>
            <div class="alert alert--error" style="margin-top:16px"><?= icon('circle-alert') ?><span>Nomor tiket <strong><?= e($ticket) ?></strong> tidak ditemukan. Periksa kembali penulisannya.</span></div>
        <?php endif; ?>



        <div class="card" style="margin-top:22px;display:flex;gap:14px;align-items:center;justify-content:space-between;flex-wrap:wrap">
            <div><strong>Punya masalah lain di sekitar Anda?</strong><div class="muted small">Laporkan sekarang, petugas desa akan menindaklanjuti.</div></div>
            <a href="/lapor" class="btn btn--lapor"><?= icon('megaphone') ?> Buat Laporan</a>
        </div>
    </div>
</section>
