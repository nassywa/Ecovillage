<?php

use function App\{e, icon, tgl, csrf_field, render};
?>
<?php if (!$rows): ?>
    <div class="card empty"><?= icon('inbox') ?><p>Belum ada pesan.</p></div>
<?php endif; ?>
<div class="list-cards">
    <?php foreach ($rows as $m): ?>
        <article class="card msg <?= $m['is_read'] ? 'is-read' : '' ?>">
            <div class="card__title">
                <div>
                    <strong><?= e($m['subject']) ?></strong>
                    <div class="small muted"><?= e($m['name']) ?><?= $m['email'] ? ' · <a href="mailto:' . e($m['email']) . '?subject=' . rawurlencode('Re: ' . $m['subject']) . '">' . e($m['email']) . '</a>' : '' ?><?= $m['phone'] ? ' · ' . e($m['phone']) : '' ?> · <?= tgl($m['created_at'], true, true) ?></div>
                </div>
                <?php if (!$m['is_read']): ?><span class="badge badge--warn">Baru</span><?php endif; ?>
            </div>
            <p class="mb-0"><?= nl2br(e($m['message'])) ?></p>
            <div class="row-actions" style="justify-content:flex-start;margin-top:12px">
                <?php if ($m['email']): ?><a class="btn btn--sm" href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode('Re: ' . $m['subject']) ?>"><?= icon('reply', 'i-sm') ?> Balas email</a><?php endif; ?>
                <?php if ($m['phone']): ?><a class="btn btn--sm" target="_blank" rel="noopener" href="https://wa.me/<?= e(preg_replace('/^0/', '62', preg_replace('/\D/', '', $m['phone']))) ?>"><?= icon('message-circle', 'i-sm') ?> Balas WA</a><?php endif; ?>
                <form method="post" action="/admin/pesan/<?= (int) $m['id'] ?>"><?= csrf_field() ?><button class="btn btn--sm btn--soft" type="submit"><?= $m['is_read'] ? 'Tandai belum dibaca' : 'Tandai sudah dibaca' ?></button></form>
                <form method="post" action="/admin/pesan/<?= (int) $m['id'] ?>" data-confirm="Hapus pesan ini?"><?= csrf_field() ?><input type="hidden" name="action" value="hapus"><button class="btn btn--sm btn--ghost" type="submit"><?= icon('trash-2', 'i-sm') ?></button></form>
            </div>
        </article>
    <?php endforeach; ?>
</div>
<?= render('partials/pagination', ['pg' => $pg]) ?>
