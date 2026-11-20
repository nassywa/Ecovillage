<?php

use function App\{e, icon, tgl, csrf_field, render, user};

$me = user();
?>
<p class="muted">Admin dapat menaikkan akun warga menjadi admin atau menghapus akun. Kode pendaftaran admin diatur di <a href="/admin/pengaturan">Pengaturan</a>.</p>
<div class="table-wrap">
    <table class="table">
        <thead><tr><th>Nama</th><th>Email</th><th>No. HP</th><th>Peran</th><th class="num">Laporan</th><th class="num">Foto</th><th>Terdaftar</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><strong><?= e($r['name']) ?></strong></td>
                <td><?= e($r['email']) ?></td>
                <td><?= e($r['phone'] ?: '-') ?></td>
                <td><span class="badge <?= $r['role'] === 'admin' ? 'badge--info' : '' ?>"><?= e(ucfirst($r['role'])) ?></span></td>
                <td class="num"><?= (int) $r['reports'] ?></td>
                <td class="num"><?= (int) $r['photos'] ?></td>
                <td><?= tgl($r['created_at'], true) ?></td>
                <td>
                    <?php if ((int) $r['id'] !== (int) $me['id']): ?>
                        <div class="row-actions">
                            <form method="post" action="/admin/pengguna/<?= (int) $r['id'] ?>" data-confirm="Ubah peran pengguna ini?">
                                <?= csrf_field() ?><input type="hidden" name="action" value="<?= $r['role'] === 'admin' ? 'warga' : 'admin' ?>">
                                <button class="btn btn--sm btn--soft" type="submit"><?= $r['role'] === 'admin' ? 'Jadikan warga' : 'Jadikan admin' ?></button>
                            </form>
                            <form method="post" action="/admin/pengguna/<?= (int) $r['id'] ?>" data-confirm="Hapus akun <?= e($r['name']) ?>?">
                                <?= csrf_field() ?><input type="hidden" name="action" value="hapus">
                                <button class="btn btn--sm btn--ghost" type="submit" aria-label="Hapus"><?= icon('trash-2', 'i-sm') ?></button>
                            </form>
                        </div>
                    <?php else: ?><span class="muted small">Akun Anda</span><?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= render('partials/pagination', ['pg' => $pg]) ?>
