<?php

use function App\{e, icon, tgl, angka, media_url, report_status_class, user, csrf_field};

$c = $counts;
?>
<p class="muted">Selamat datang, <strong><?= e(user()['name']) ?></strong>. Berikut ringkasan aktivitas website desa.</p>

<div class="grid-4" style="margin-bottom:18px">
    <a class="card kpi" href="/admin/laporan?status=Menunggu"><span class="icon-card__icon" style="background:var(--accent-soft);color:#B26A00"><?= icon('megaphone') ?></span><div><div class="kpi__num"><?= angka($c['reports_new']) ?></div><div class="kpi__label">Laporan baru menunggu</div></div></a>
    <a class="card kpi" href="/admin/laporan"><span class="icon-card__icon"><?= icon('loader') ?></span><div><div class="kpi__num"><?= angka($c['reports_open']) ?></div><div class="kpi__label">Laporan belum selesai</div></div></a>
    <a class="card kpi" href="/admin/kelola/galeri?status=Menunggu"><span class="icon-card__icon" style="background:var(--info-soft);color:var(--info)"><?= icon('images') ?></span><div><div class="kpi__num"><?= angka($c['gallery_pending']) ?></div><div class="kpi__label">Foto warga perlu persetujuan</div></div></a>
    <a class="card kpi" href="/admin/pesan"><span class="icon-card__icon"><?= icon('inbox') ?></span><div><div class="kpi__num"><?= angka($c['messages_unread']) ?></div><div class="kpi__label">Pesan belum dibaca</div></div></a>
</div>

<div class="split">
    <div class="stack">
        <div class="card">
            <div class="card__title"><h3 class="mb-0">Laporan Terbaru</h3><a href="/admin/laporan" class="link-more">Semua laporan</a></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Tiket</th><th>Kategori</th><th>Lokasi</th><th>Tanggal</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($latestReports as $r): ?>
                        <tr>
                            <td><a href="/admin/laporan/<?= (int) $r['id'] ?>"><strong><?= e($r['ticket']) ?></strong></a></td>
                            <td><?= e($r['category']) ?></td>
                            <td><?= e($r['location']) ?></td>
                            <td><?= tgl($r['created_at'], true) ?></td>
                            <td><span class="badge <?= report_status_class($r['status']) ?>"><?= e($r['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if ($pendingGallery): ?>
            <div class="card">
                <div class="card__title"><h3 class="mb-0">Foto Warga Menunggu Persetujuan</h3><a href="/admin/kelola/galeri?status=Menunggu" class="link-more">Kelola</a></div>
                <div class="grid-2">
                    <?php foreach ($pendingGallery as $g): ?>
                        <div class="media-item">
                            <img class="media-item__thumb" src="<?= media_url($g['image_id']) ?>" alt="" data-lightbox data-caption="<?= e($g['title']) ?>">
                            <div>
                                <div class="media-item__title"><?= e($g['title']) ?></div>
                                <div class="media-item__meta">oleh <?= e($g['uploader_name']) ?></div>
                                <div class="row-actions" style="justify-content:flex-start;margin-top:6px">
                                    <form method="post" action="/admin/galeri/<?= (int) $g['id'] ?>/status"><?= csrf_field() ?><input type="hidden" name="status" value="Disetujui"><button class="btn btn--sm">Setujui</button></form>
                                    <form method="post" action="/admin/galeri/<?= (int) $g['id'] ?>/status"><?= csrf_field() ?><input type="hidden" name="status" value="Ditolak"><button class="btn btn--sm btn--ghost">Tolak</button></form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="stack">
        <div class="card chart-card">
            <h3>Status Laporan</h3>
            <div class="chart-box chart-box--round"><canvas data-chart='<?= e(json_encode(['type' => 'doughnut', 'unit' => 'laporan', 'labels' => array_column($byStatus, 'status'), 'datasets' => [['label' => 'Laporan', 'data' => array_map('intval', array_column($byStatus, 'n'))]]])) ?>'></canvas></div>
        </div>
        <div class="card chart-card">
            <h3>Laporan per Kategori</h3>
            <div class="chart-box"><canvas data-chart='<?= e(json_encode(['type' => 'hbar', 'unit' => 'laporan', 'labels' => array_column($byCategory, 'category'), 'datasets' => [['label' => 'Laporan', 'data' => array_map('intval', array_column($byCategory, 'n'))]]])) ?>'></canvas></div>
        </div>
        <div class="card">
            <h3>Konten Website</h3>
            <dl class="kv">
                <dt>Berita terbit</dt><dd><?= angka($c['news_total']) ?></dd>
                <dt>Tempat & produk</dt><dd><?= angka($c['places_total']) ?></dd>
                <dt>Akun warga</dt><dd><?= angka($c['users_total']) ?></dd>
                <dt>Total laporan</dt><dd><?= angka($c['reports_total']) ?></dd>
            </dl>
            <div class="row-actions" style="justify-content:flex-start;margin-top:12px">
                <a class="btn btn--sm" href="/admin/kelola/berita/baru"><?= icon('plus', 'i-sm') ?> Tulis berita</a>
                <a class="btn btn--sm btn--soft" href="/admin/kelola/galeri/baru"><?= icon('image-plus', 'i-sm') ?> Tambah foto</a>
            </div>
        </div>
    </div>
</div>
