<?php use function App\{e, icon, render}; ?>
<?= render('partials/page_hero', ['kicker' => 'Kode ' . (int) $code, 'heading' => $title, 'text' => $message]) ?>
<section class="section section--lg"><div class="container center">
    <a class="btn btn--pill" href="/"><?= icon('house') ?> Kembali ke Beranda</a>
</div></section>
