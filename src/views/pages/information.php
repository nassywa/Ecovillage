<?php

use function App\{e, icon, setting, angka, render};

$sections = array_values(array_filter($sections, fn ($s) => $s['code'] !== 'Z'));
$codes = array_column($sections, 'code');
if (!in_array($active, $codes, true)) {
    $active = $codes[0] ?? 'A';
}
?>
<?= render('partials/page_hero', ['kicker' => 'Data terbuka', 'heading' => 'Informasi Desa', 'text' => 'Data dan informasi lengkap Desa Marga Mulya.']) ?>

<section class="section section--lg">
    <div class="container">
        <div class="section-head--split">
            <div><span class="kicker">Data terbuka</span><h2>Informasi Desa</h2></div>
            <p>Pilih salah satu kategori untuk membuka grafik dan rincian angkanya.</p>
        </div>

        <div class="cat-grid" role="tablist" aria-label="Kategori informasi desa">
            <?php foreach ($sections as $s): $n = count($bySection[$s['id']] ?? []); $sel = $s['code'] === $active; ?>
                <a class="cat-card" role="tab" id="tab-<?= e($s['code']) ?>" href="?bagian=<?= e($s['code']) ?>#data"
                   aria-selected="<?= $sel ? 'true' : 'false' ?>" aria-controls="panel-<?= e($s['code']) ?>" data-section-tab="<?= e($s['code']) ?>">
                    <span class="cat-card__letter"><?= e($s['code']) ?></span>
                    <span class="tile-icon tile-icon--soft"><?= icon($s['icon']) ?></span>
                    <strong><?= e($s['title']) ?></strong>
                    <span class="small"><?= $n ?> set data</span>
                </a>
            <?php endforeach; ?>
        </div>

        <div id="data" style="scroll-margin-top:100px"></div>
        <?php foreach ($sections as $s): $groups = $bySection[$s['id']] ?? []; ?>
            <div class="stat-panel" role="tabpanel" id="panel-<?= e($s['code']) ?>" aria-labelledby="tab-<?= e($s['code']) ?>" data-section-panel="<?= e($s['code']) ?>" <?= $s['code'] === $active ? '' : 'hidden' ?>>
                <div class="section-head" style="margin-top:34px">
                    <div>
                        <span class="kicker">Kategori <?= e($s['code']) ?></span>
                        <h2><?= e($s['title']) ?></h2>
                        <p class="muted mb-0"><?= e($s['description']) ?></p>
                    </div>
                </div>
                <?php if (!$groups): ?>
                    <div class="card empty"><?= icon('chart-column') ?><p>Data untuk kategori ini belum tersedia.</p></div>
                <?php endif; ?>
                <div class="grid-2">
                    <?php foreach ($groups as $g): $c = $g['chart']; $multi = count($c['datasets']) > 1; ?>
                        <article class="card chart-card" style="padding:20px">
                            <div class="chart-card__head">
                                <div>
                                    <h3><?= e($g['title']) ?></h3>
                                    <?php if ($g['description']): ?><p class="chart-total mb-0"><?= e($g['description']) ?></p><?php endif; ?>
                                </div>
                                <?php if (!$multi && $c['type'] !== 'line' && $c['unit'] !== '%' && $c['type'] !== 'cards'): ?>
                                    <span class="badge">Total <?= angka(array_sum($c['datasets'][0]['data'] ?? [])) ?> <?= e($c['unit']) ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if ($c['type'] === 'cards'): ?>
                                <div class="mini-cards">
                                    <?php foreach ($c['labels'] as $i => $label): ?>
                                        <div class="mini-card">
                                            <div class="mini-card__num"><?= angka($c['datasets'][0]['data'][$i]) ?></div>
                                            <div class="mini-card__label"><?= e($label) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <?php if ($c['type'] !== 'table'): ?>
                                    <?php $round = in_array($c['type'], ['pie', 'doughnut'], true);
                                          $h = $c['type'] === 'hbar' ? max(220, count($c['labels']) * 34) : 0; ?>
                                    <div class="chart-box <?= $round ? 'chart-box--round' : '' ?>" <?= $h ? 'style="height:' . $h . 'px"' : '' ?>>
                                        <canvas data-chart='<?= e(json_encode($c, JSON_UNESCAPED_UNICODE)) ?>' role="img" aria-label="Grafik <?= e($g['title']) ?>"></canvas>
                                    </div>
                                <?php endif; ?>
                                <details <?= $c['type'] === 'table' ? 'open' : '' ?>>
                                    <summary>Lihat rincian angka</summary>
                                    <div class="table-wrap" style="margin-top:8px">
                                        <table class="table">
                                            <thead><tr><th><?= $c['type'] === 'line' ? 'Tahun' : 'Uraian' ?></th>
                                                <?php foreach ($c['datasets'] as $ds): ?><th class="num"><?= e($ds['label']) ?><?= $c['unit'] ? ' (' . e($c['unit']) . ')' : '' ?></th><?php endforeach; ?>
                                            </tr></thead>
                                            <tbody>
                                            <?php foreach ($c['labels'] as $i => $label): ?>
                                                <tr><td><?= e($label) ?></td>
                                                    <?php foreach ($c['datasets'] as $ds): ?><td class="num"><?= angka($ds['data'][$i]) ?></td><?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </details>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <p class="help" style="margin-top:20px"><?= icon('info', 'i-sm') ?> Data bersumber dari pendataan pemerintah desa (data contoh) dan diperbarui oleh admin melalui CMS. Penggunaan lahan ditampilkan di <a href="/profil#wilayah">Profil Desa</a>.</p>
    </div>
</section>
