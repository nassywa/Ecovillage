<?php

use function App\{e, icon, tgl, render, report_status_class};
?>
<div class="toolbar">
    <form method="get" role="search">
        <input class="input" type="search" name="cari" value="<?= e($search) ?>" placeholder="Cari tiket, lokasi, pelapor…">
        <select class="select" name="status">
            <option value="">Semua status</option>
            <?php foreach (\App\REPORT_STATUSES as $s): ?><option <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
        </select>
        <select class="select" name="kategori">
            <option value="">Semua kategori</option>
            <?php foreach ($categories as $c): ?><option <?= $cat === $c['name'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
        <button class="btn btn--sm" type="submit"><?= icon('filter', 'i-sm') ?> Terapkan</button>
    </form>
    <span class="muted small"><?= (int) $pg['total'] ?> laporan</span>
</div>
<div class="table-wrap">
    <table class="table">
        <thead><tr><th>Tiket</th><th>Kategori</th><th>Lokasi</th><th>Pelapor</th><th>Dikirim</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><strong><?= e($r['ticket']) ?></strong></td>
                <td><?= e($r['category']) ?></td>
                <td><?= e($r['location']) ?></td>
                <td><?= e($r['reporter_name'] ?: '-') ?></td>
                <td><?= tgl($r['created_at'], true) ?></td>
                <td><span class="badge <?= report_status_class($r['status']) ?>"><?= e($r['status']) ?></span></td>
                <td><a class="btn btn--sm" href="/admin/laporan/<?= (int) $r['id'] ?>">Tindak lanjut</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="7" class="center muted">Tidak ada laporan.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?= render('partials/pagination', ['pg' => $pg]) ?>
