<?php

use function App\{e, icon, csrf_field, angka};

$preview = \App\Pub\chart_payload($group, $items);
?>
<p><a href="/admin/statistik"><?= icon('arrow-left', 'i-sm') ?> Kembali</a> · Bagian <?= e($group['code'] !== 'Z' ? $group['code'] . '. ' : '') ?><?= e($group['section_title']) ?></p>
<div class="split">
    <form class="card" method="post">
        <?= csrf_field() ?>
        <h3>Data grafik</h3>
        <p class="help">Isi <strong>Label</strong> (mis. "Petani") dan <strong>Angka</strong>. Kolom <strong>Seri</strong> dipakai bila satu label punya beberapa nilai (mis. "Laki-laki" dan "Perempuan", atau "Target" dan "Realisasi"). Kosongkan bila hanya satu nilai.</p>
        <div class="table-wrap rows-table">
            <table class="table">
                <thead><tr><th>Label</th><th>Seri (opsional)</th><th>Angka<?= $group['unit'] ? ' (' . e($group['unit']) . ')' : '' ?></th><th></th></tr></thead>
                <tbody data-rows>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td><input class="input" name="label[]" value="<?= e($it['label']) ?>" required aria-label="Label"></td>
                        <td><input class="input" name="series[]" value="<?= e($it['series']) ?>" aria-label="Seri"></td>
                        <td><input class="input" name="value[]" value="<?= e(rtrim(rtrim($it['value'], '0'), '.')) ?>" inputmode="decimal" required aria-label="Angka" style="max-width:130px"></td>
                        <td><button class="icon-btn" type="button" data-remove-row aria-label="Hapus baris"><?= icon('trash-2', 'i-sm') ?></button></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <template id="row-template">
            <tr>
                <td><input class="input" name="label[]" required aria-label="Label"></td>
                <td><input class="input" name="series[]" aria-label="Seri"></td>
                <td><input class="input" name="value[]" inputmode="decimal" required aria-label="Angka" style="max-width:130px"></td>
                <td><button class="icon-btn" type="button" data-remove-row aria-label="Hapus baris"><?= icon('trash-2', 'i-sm') ?></button></td>
            </tr>
        </template>
        <div class="form-actions">
            <button class="btn btn--soft" type="button" data-add-row><?= icon('plus', 'i-sm') ?> Tambah baris</button>
            <button class="btn" type="submit"><?= icon('save') ?> Simpan data</button>
        </div>
    </form>
    <div class="card chart-card sticky-side">
        <h3>Pratinjau</h3>
        <p class="help mb-0">Pratinjau diperbarui setelah data disimpan.</p>
        <?php if ($preview['type'] === 'cards' || $preview['type'] === 'table'): ?>
            <div class="mini-cards" style="grid-template-columns:repeat(2,1fr)">
                <?php foreach ($preview['labels'] as $i => $l): ?><div class="mini-card"><div class="mini-card__num"><?= angka($preview['datasets'][0]['data'][$i]) ?></div><div class="mini-card__label"><?= e($l) ?></div></div><?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="chart-box"><canvas data-chart='<?= e(json_encode($preview, JSON_UNESCAPED_UNICODE)) ?>'></canvas></div>
        <?php endif; ?>
        <a class="btn btn--ghost btn--sm" style="margin-top:12px" href="/admin/kelola/grafik/<?= (int) $group['id'] ?>"><?= icon('settings', 'i-sm') ?> Ubah judul / jenis grafik</a>
    </div>
</div>
