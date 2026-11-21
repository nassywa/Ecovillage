<?php

use function App\{e, icon, tgl, csrf_field, media_url, report_status_class};
?>
<p><a href="/admin/laporan"><?= icon('arrow-left', 'i-sm') ?> Kembali ke daftar laporan</a></p>
<div class="split">
    <div class="stack">
        <div class="card">
            <div class="report-card__head">
                <span class="report-card__ticket">#<?= e($r['ticket']) ?></span>
                <span class="badge <?= report_status_class($r['status']) ?>"><?= e($r['status']) ?></span>
            </div>
            <dl class="kv" style="margin-top:12px">
                <dt>Kategori</dt><dd><?= e($r['category']) ?></dd>
                <dt>Lokasi</dt><dd><?= e($r['location']) ?></dd>
                <dt>Pelapor</dt><dd><?= e($r['reporter_name'] ?: 'Tanpa nama') ?><?= $r['user_email'] ? ' · ' . e($r['user_email']) : '' ?></dd>
                <dt>No. HP</dt><dd><?php if ($r['reporter_phone']): $wa = preg_replace('/^0/', '62', preg_replace('/\D/', '', $r['reporter_phone'])); ?><a href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener"><?= e($r['reporter_phone']) ?></a><?php else: ?>-<?php endif; ?></dd>
                <dt>Dikirim</dt><dd><?= tgl($r['created_at'], false, true) ?></dd>
                <dt>Diterima</dt><dd><?= $r['accepted_at'] ? tgl($r['accepted_at'], false, true) : '-' ?></dd>
                <dt>Selesai</dt><dd><?= $r['finished_at'] ? tgl($r['finished_at'], false, true) : '-' ?></dd>
            </dl>
            <h3 style="margin-top:16px">Deskripsi</h3>
            <p><?= nl2br(e($r['description'])) ?></p>
            <?php if ($r['photo_id']): ?>
                <img class="cover" style="aspect-ratio:4/3;max-width:460px" src="<?= media_url($r['photo_id']) ?>" alt="Foto laporan" data-lightbox data-caption="Foto laporan #<?= e($r['ticket']) ?>">
            <?php endif; ?>
        </div>
        <div class="card">
            <h3>Riwayat</h3>
            <ol class="timeline">
                <?php foreach ($history as $h): ?>
                    <li><strong><?= e($h['status']) ?></strong> <span class="timeline__time"><?= tgl($h['created_at'], true, true) ?></span><?php if ($h['note']): ?><div class="small"><?= e($h['note']) ?></div><?php endif; ?></li>
                <?php endforeach; ?>
            </ol>
        </div>
    </div>
    <div class="stack sticky-side">
        <form class="card" method="post">
            <?= csrf_field() ?>
            <h3>Perbarui Status</h3>
            <div class="field">
                <label for="status">Status</label>
                <select class="select" id="status" name="status">
                    <?php foreach (\App\REPORT_STATUSES as $s): ?><option <?= $r['status'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="note">Catatan untuk warga</label>
                <textarea class="textarea" id="note" name="note" placeholder="Contoh: Petugas akan memperbaiki pada hari Senin."></textarea>
                <span class="help">Catatan tampil di halaman Cek Status.</span>
            </div>
            <button class="btn btn--block" type="submit"><?= icon('save') ?> Simpan</button>
        </form>
        <form class="card" method="post" data-confirm="Hapus laporan ini secara permanen?">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="hapus">
            <p class="small muted">Hapus hanya untuk laporan palsu atau duplikat.</p>
            <button class="btn btn--danger btn--sm" type="submit"><?= icon('trash-2', 'i-sm') ?> Hapus laporan</button>
        </form>
        <a class="btn btn--ghost" href="/cek-status?tiket=<?= e($r['ticket']) ?>" target="_blank"><?= icon('external-link', 'i-sm') ?> Lihat tampilan publik</a>
    </div>
</div>
