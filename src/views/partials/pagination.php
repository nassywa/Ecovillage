<?php

use function App\{qs, icon};

if (($pg['pages'] ?? 1) <= 1) {
    return;
}
$page = $pg['page'];
$pages = $pg['pages'];
$window = [];
for ($i = 1; $i <= $pages; $i++) {
    if ($i === 1 || $i === $pages || abs($i - $page) <= 1) {
        $window[] = $i;
    } elseif (end($window) !== '…') {
        $window[] = '…';
    }
}
?>
<nav class="pagination<?= !empty($dots) ? ' pagination--dots' : '' ?>" aria-label="Halaman">
    <?php if ($page > 1): ?>
        <a href="<?= \App\e(qs(['hal' => $page - 1])) ?>" rel="prev" class="pg-arrow"><?= icon('chevron-left', 'i-sm') ?> Prev</a>
    <?php else: ?>
        <span class="is-disabled pg-arrow"><?= icon('chevron-left', 'i-sm') ?> Prev</span>
    <?php endif; ?>
    <?php foreach ($window as $w): ?>
        <?php if ($w === '…'): ?>
            <span aria-hidden="true">…</span>
        <?php elseif ($w === $page): ?>
            <span class="is-current" aria-current="page"><?= $w ?></span>
        <?php else: ?>
            <a href="<?= \App\e(qs(['hal' => $w])) ?>"><?= $w ?></a>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if ($page < $pages): ?>
        <a href="<?= \App\e(qs(['hal' => $page + 1])) ?>" rel="next" class="pg-arrow">Next <?= icon('chevron-right', 'i-sm') ?></a>
    <?php else: ?>
        <span class="is-disabled pg-arrow">Next <?= icon('chevron-right', 'i-sm') ?></span>
    <?php endif; ?>
</nav>
