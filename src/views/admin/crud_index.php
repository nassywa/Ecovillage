<?php

use function App\{e, icon, tgl, csrf_field, media_url, render, excerpt, qs};

$key = $ent['key'];
$imageCols = array_keys(array_filter($ent['fields'], fn ($f) => $f['type'] === 'image'));
$statusClass = fn ($v) => match ($v) {
    'Terbit', 'Tampil', 'Disetujui' => 'badge--ok',
    'Draft', 'Sembunyi', 'Menunggu' => 'badge--warn',
    'Ditolak' => 'badge--danger',
    default => '',
};
$publicLinks = ['berita' => '/berita', 'jelajah' => '/jelajah', 'galeri' => '/galeri', 'perangkat' => '/profil#pemerintahan', 'wilayah' => '/profil#wilayah', 'titik' => '/profil#wilayah', 'jam' => '/kontak', 'kategori-laporan' => '/lapor', 'bagian-data' => '/informasi', 'grafik' => '/informasi'];
?>
<div class="toolbar">
    <form method="get" role="search">
        <?php if (!empty($ent['search'])): ?>
            <input class="input" type="search" name="cari" value="<?= e($search) ?>" placeholder="Cari…">
        <?php endif; ?>
        <?php foreach ($ent['filters'] ?? [] as $col => $opts): ?>
            <select class="select" name="<?= e($col) ?>" onchange="this.form.submit()">
                <option value=""><?= e($ent['fields'][$col]['label'] ?? ucfirst($col)) ?>: semua</option>
                <?php foreach ($opts as $o): ?><option value="<?= e($o) ?>" <?= ($_GET[$col] ?? '') === $o ? 'selected' : '' ?>><?= e(ucfirst($o)) ?></option><?php endforeach; ?>
            </select>
        <?php endforeach; ?>
        <?php if (!empty($ent['search'])): ?><button class="btn btn--sm btn--soft" type="submit"><?= icon('search', 'i-sm') ?></button><?php endif; ?>
    </form>
    <div class="row-actions">
        <?php if (isset($publicLinks[$key])): ?><a class="btn btn--sm btn--ghost" href="<?= $publicLinks[$key] ?>" target="_blank"><?= icon('external-link', 'i-sm') ?> Lihat di website</a><?php endif; ?>
        <a class="btn btn--sm" href="/admin/kelola/<?= e($key) ?>/baru"><?= icon('plus', 'i-sm') ?> Tambah <?= e($ent['singular']) ?></a>
    </div>
</div>

<div class="table-wrap">
    <table class="table">
        <thead><tr>
            <?php foreach ($ent['columns'] as $col => $label): ?><th><?= e($label) ?></th><?php endforeach; ?>
            <th></th>
        </tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <?php foreach ($ent['columns'] as $col => $label): $v = $r[$col] ?? null; $f = $ent['fields'][$col] ?? ['type' => 'text']; ?>
                    <td>
                        <?php if ($f['type'] === 'image'): ?>
                            <img class="thumb" src="<?= media_url($v) ?>" alt="" <?= $v ? 'data-lightbox data-caption="' . e($r['title'] ?? $r['name'] ?? '') . '"' : '' ?>>
                        <?php elseif ($f['type'] === 'checkbox'): ?>
                            <?= $v ? '<span class="badge badge--ok">Ya</span>' : '<span class="muted">—</span>' ?>
                        <?php elseif ($f['type'] === 'color'): ?>
                            <span class="swatch" style="background:<?= e($v) ?>"></span>
                        <?php elseif ($f['type'] === 'icon'): ?>
                            <?= icon((string) $v) ?>
                        <?php elseif ($f['type'] === 'date'): ?>
                            <?= tgl($v, true) ?>
                        <?php elseif ($f['type'] === 'relation'): ?>
                            <?= e($relations[$col][$v] ?? '-') ?>
                        <?php elseif (isset($ent['labels'][$col])): ?>
                            <?= e($ent['labels'][$col][$v] ?? $v) ?>
                        <?php elseif (in_array($col, ['status'], true)): ?>
                            <span class="badge <?= $statusClass($v) ?>"><?= e($v) ?></span>
                        <?php elseif ($f['type'] === 'select' && !array_is_list($f['options'] ?? [])): ?>
                            <?= e($f['options'][$v] ?? $v) ?>
                        <?php elseif ($col === 'title' || $col === 'name'): ?>
                            <a href="/admin/kelola/<?= e($key) ?>/<?= (int) $r['id'] ?>"><strong><?= e(excerpt((string) $v, 70)) ?></strong></a>
                        <?php else: ?>
                            <?= e(excerpt((string) $v, 60)) ?: '<span class="muted">—</span>' ?>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
                <td>
                    <div class="row-actions">
                        <?php if (($ent['row_actions'] ?? '') === 'gallery' && $r['status'] !== 'Disetujui'): ?>
                            <form method="post" action="/admin/galeri/<?= (int) $r['id'] ?>/status"><?= csrf_field() ?><input type="hidden" name="status" value="Disetujui"><button class="btn btn--sm" type="submit"><?= icon('check', 'i-sm') ?> Setujui</button></form>
                        <?php endif; ?>
                        <?php if (($ent['row_actions'] ?? '') === 'gallery' && $r['status'] === 'Menunggu'): ?>
                            <form method="post" action="/admin/galeri/<?= (int) $r['id'] ?>/status"><?= csrf_field() ?><input type="hidden" name="status" value="Ditolak"><button class="btn btn--sm btn--ghost" type="submit">Tolak</button></form>
                        <?php endif; ?>
                        <?php if ($key === 'grafik'): ?><a class="btn btn--sm" href="/admin/statistik/<?= (int) $r['id'] ?>">Data</a><?php endif; ?>
                        <a class="btn btn--sm btn--soft" href="/admin/kelola/<?= e($key) ?>/<?= (int) $r['id'] ?>"><?= icon('pencil', 'i-sm') ?> Ubah</a>
                        <form method="post" action="/admin/kelola/<?= e($key) ?>/<?= (int) $r['id'] ?>/hapus" data-confirm="Hapus data ini? Tindakan tidak dapat dibatalkan.">
                            <?= csrf_field() ?>
                            <button class="btn btn--sm btn--ghost" type="submit" aria-label="Hapus"><?= icon('trash-2', 'i-sm') ?></button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="<?= count($ent['columns']) + 1 ?>" class="center muted" style="padding:30px">Belum ada data.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<p class="small muted" style="margin-top:8px"><?= (int) $pg['total'] ?> data</p>
<?= render('partials/pagination', ['pg' => $pg]) ?>
