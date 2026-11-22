<?php use function App\e; ?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
<title>Instalasi Database — EcoVillage</title><link rel="stylesheet" href="/assets/css/app.css"></head>
<body>
<main class="container" style="max-width:560px;padding:40px 18px">
    <div class="card">
        <h1 style="font-size:1.5rem">Instalasi Database EcoVillage</h1>
        <p class="muted">Halaman ini membuat tabel dan mengisi data contoh Desa Marga Mulya pada database kosong. Database yang sudah berisi tabel akan ditolak demi menjaga data lama.</p>
        <?php if ($message): ?>
            <div class="alert alert--<?= $ok ? 'success' : 'error' ?>"><span><?= e($message) ?></span></div>
        <?php endif; ?>
        <?php if ($ok): ?>
            <a class="btn" href="/">Buka website</a> <a class="btn btn--soft" href="/masuk?peran=admin">Masuk admin</a>
            <p class="help" style="margin-top:12px">Demi keamanan, ganti atau hapus SETUP_KEY di Environment Variables Vercel setelah instalasi.</p>
        <?php elseif (!$enabled): ?>
            <div class="alert alert--info"><span>Instalasi dinonaktifkan. Atur Environment Variable <code>SETUP_KEY</code> terlebih dahulu.</span></div>
        <?php else: ?>
            <form method="post" action="/setup">
                <div class="field"><label for="key">Kunci setup (SETUP_KEY)</label><input class="input" type="password" id="key" name="key" required></div>
                <div class="field"><label for="email">Email admin</label><input class="input" type="email" id="email" name="email" value="admin@margamulya.id" required></div>
                <div class="field"><label for="password">Kata sandi admin (min. 8 karakter)</label><input class="input" type="password" id="password" name="password" minlength="8" required></div>
                <button class="btn btn--block" type="submit" onclick="return confirm('Semua data lama akan dihapus dan diganti data contoh. Lanjutkan?')">Instal database</button>
            </form>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
