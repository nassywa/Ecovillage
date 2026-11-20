<?php

use function App\e;

/** @var array $hours */
$hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][(int) date('w')];
$nowMin = (int) date('G') * 60 + (int) date('i');
$isOpen = false;
foreach ($hours as $h) {
    if ($h['day'] === $hari && !$h['is_closed'] && $h['open_time'] && $h['close_time']) {
        [$oh, $om] = array_map('intval', preg_split('/[.:]/', $h['open_time']) + [0, 0]);
        [$ch, $cm] = array_map('intval', preg_split('/[.:]/', $h['close_time']) + [0, 0]);
        $isOpen = $nowMin >= $oh * 60 + $om && $nowMin < $ch * 60 + $cm;
    }
}
?>
<div class="card__title">
    <h3 class="mb-0"><?= e($hoursTitle ?? 'Jam Kerja') ?></h3>
    <span class="open-badge <?= $isOpen ? 'is-open' : 'is-closed' ?>"><?= $isOpen ? 'Sedang buka' : 'Sedang tutup' ?></span>
</div>
<ul class="hours">
    <?php foreach ($hours as $h): ?>
        <li class="<?= $h['day'] === $hari ? 'is-today' : '' ?>">
            <span><?= e($h['day']) ?></span>
            <?php if ($h['is_closed']): ?>
                <span class="closed">Libur</span>
            <?php else: ?>
                <span><?= e($h['open_time']) ?>–<?= e($h['close_time']) ?> WIB</span>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>
