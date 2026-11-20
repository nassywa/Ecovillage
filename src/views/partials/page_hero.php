<?php use function App\e; ?>
<section class="page-hero">
    <div class="container">
        <span class="kicker"><?= e($kicker ?? '') ?></span>
        <h1><?= e($heading ?? '') ?></h1>
        <?php if (!empty($text)): ?><p><?= e($text) ?></p><?php endif; ?>
    </div>
</section>
