<?php

use function App\{e, icon};

$types = ['bar' => 'Batang', 'hbar' => 'Batang horizontal', 'stacked' => 'Batang bertumpuk', 'pie' => 'Lingkaran', 'doughnut' => 'Donat', 'line' => 'Garis', 'cards' => 'Kartu angka', 'table' => 'Tabel'];
?>
<div class="toolbar">
    <p class="muted mb-0">Ubah angka pada setiap grafik di halaman <a href="/informasi" target="_blank">Informasi Desa</a>. Klik judul grafik untuk mengedit datanya.</p>
    <div class="row-actions">
        <a class="btn btn--sm btn--soft" href="/admin/kelola/bagian-data"><?= icon('layers', 'i-sm') ?> Kelola bagian</a>
        <a class="btn btn--sm" href="/admin/kelola/grafik/baru"><?= icon('plus', 'i-sm') ?> Grafik baru</a>
    </div>
</div>
<?php foreach ($sections as $s): ?>
    <section class="card" style="margin-bottom:14px">
        <div class="card__title">
            <h3 class="mb-0"><?= icon($s['icon']) ?> <?= $s['code'] !== 'Z' ? e($s['code']) . '. ' : '' ?><?= e($s['title']) ?></h3>
            <a class="btn btn--sm btn--ghost" href="/admin/kelola/bagian-data/<?= (int) $s['id'] ?>">Ubah bagian</a>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Judul grafik</th><th>Tampilan</th><th>Satuan</th><th class="num">Baris data</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($by[$s['id']] ?? [] as $g): ?>
                    <tr>
                        <td><a href="/admin/statistik/<?= (int) $g['id'] ?>"><strong><?= e($g['title']) ?></strong></a></td>
                        <td><?= e($types[$g['chart_type']] ?? $g['chart_type']) ?></td>
                        <td><?= e($g['unit']) ?></td>
                        <td class="num"><?= (int) $g['items'] ?></td>
                        <td><div class="row-actions">
                            <a class="btn btn--sm" href="/admin/statistik/<?= (int) $g['id'] ?>">Edit data</a>
                            <a class="btn btn--sm btn--ghost" href="/admin/kelola/grafik/<?= (int) $g['id'] ?>">Pengaturan</a>
                        </div></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($by[$s['id']])): ?><tr><td colspan="5" class="muted">Belum ada grafik.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endforeach; ?>
