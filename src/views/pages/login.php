<?php

use function App\{e, icon, csrf_field, old};

$r = old('role', $role);
?>
<section class="auth-hero">
    <div class="container auth-grid">
        <div>
            <span class="kicker" style="color:rgba(255,255,255,.8)">Akun warga</span>
            <h1>Masuk ke Desa Marga Mulya</h1>
            <p>Dengan akun, Anda dapat mengirim Lapor Desa dan membagikan foto desa. Admin desa mengelola konten lewat panel CMS.</p>
            <ul class="auth-points">
                <li><?= icon('megaphone', 'i-sm') ?> Kirim laporan &amp; dapatkan nomor tiket</li>
                <li><?= icon('image', 'i-sm') ?> Unggah foto ke galeri desa</li>
                <li><?= icon('shield-check', 'i-sm') ?> Data Anda disimpan dengan aman</li>
            </ul>
        </div>

        <div class="auth-card">
            <div class="seg-full" role="tablist">
                <button type="button" role="tab" data-tab="masuk" aria-selected="<?= $tab === 'masuk' ? 'true' : 'false' ?>">Masuk</button>
                <button type="button" role="tab" data-tab="daftar" aria-selected="<?= $tab === 'daftar' ? 'true' : 'false' ?>">Daftar</button>
            </div>

            <form method="post" action="/masuk" data-tab-panel="masuk" <?= $tab === 'masuk' ? '' : 'hidden' ?>>
                <?= csrf_field() ?>
                <input type="hidden" name="kembali" value="<?= e($back) ?>">
                <div class="seg-full" role="radiogroup" aria-label="Masuk sebagai">
                    <label><input type="radio" name="role" value="warga" <?= $role === 'warga' ? 'checked' : '' ?>> Warga</label>
                    <label><input type="radio" name="role" value="admin" <?= $role === 'admin' ? 'checked' : '' ?>> Admin</label>
                </div>
                <div class="field"><label class="sr-only" for="l-email">Email</label><input class="input" type="email" id="l-email" name="email" required autocomplete="email" placeholder="Email" value="<?= e(old('email')) ?>"></div>
                <div class="field">
                    <label class="sr-only" for="l-pass">Kata sandi</label>
                    <div class="input-group">
                        <input class="input" type="password" id="l-pass" name="password" required autocomplete="current-password" placeholder="Kata sandi">
                        <button class="icon-btn" type="button" data-toggle-pass="l-pass" aria-label="Tampilkan kata sandi"><?= icon('eye') ?></button>
                    </div>
                </div>
                <button class="btn btn--block btn--pill" type="submit" style="min-height:48px">Masuk</button>
                <p class="small center" style="margin:12px 0 0">Belum punya akun? <a href="#" data-tab-link="daftar"><strong>Daftar sekarang</strong></a></p>
                <p class="help center" style="margin-top:6px">Belum memiliki akun? Gunakan formulir pendaftaran di samping.</p>
            </form>

            <form method="post" action="/daftar" data-tab-panel="daftar" <?= $tab === 'daftar' ? '' : 'hidden' ?>>
                <?= csrf_field() ?>
                <div class="seg-full" role="radiogroup" aria-label="Daftar sebagai">
                    <label><input type="radio" name="role" value="warga" <?= $r !== 'admin' ? 'checked' : '' ?> data-role-radio> Warga</label>
                    <label><input type="radio" name="role" value="admin" <?= $r === 'admin' ? 'checked' : '' ?> data-role-radio> Admin</label>
                </div>
                <div class="field"><label class="sr-only" for="r-name">Nama lengkap</label><input class="input" id="r-name" name="name" required maxlength="100" autocomplete="name" placeholder="Nama lengkap" value="<?= e(old('name')) ?>"></div>
                <div class="field"><label class="sr-only" for="r-email">Email</label><input class="input" type="email" id="r-email" name="email" required autocomplete="email" placeholder="Email" value="<?= e(old('email')) ?>"></div>
                <div class="field"><label class="sr-only" for="r-phone">No. HP</label><input class="input" id="r-phone" name="phone" inputmode="tel" maxlength="20" autocomplete="tel" placeholder="No. HP (opsional)" value="<?= e(old('phone')) ?>"></div>
                <div class="grid-2" style="gap:0 10px">
                    <div class="field"><label class="sr-only" for="r-pass">Kata sandi</label><input class="input" type="password" id="r-pass" name="password" required minlength="8" autocomplete="new-password" placeholder="Kata sandi (min. 8)"></div>
                    <div class="field"><label class="sr-only" for="r-pass2">Ulangi kata sandi</label><input class="input" type="password" id="r-pass2" name="password_confirm" required minlength="8" autocomplete="new-password" placeholder="Ulangi kata sandi"></div>
                </div>
                <div class="field" data-admin-only <?= $r === 'admin' ? '' : 'hidden' ?>>
                    <label for="r-code">Kode pendaftaran admin</label>
                    <input class="input" id="r-code" name="admin_code" autocomplete="off">
                    <span class="help">Pendaftaran admin memerlukan kode dari pengelola website desa.</span>
                </div>
                <button class="btn btn--block btn--pill" type="submit" style="min-height:48px">Buat Akun</button>
            </form>
        </div>
    </div>
</section>
