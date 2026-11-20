<?php

use function App\{e, icon, setting, csrf_field, media_url};
?>
<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" data-upload-form>
    <?= csrf_field() ?>
    <?php foreach ($schema as $group => $fields): ?>
        <section class="card" style="margin-bottom:16px">
            <h3><?= e($group) ?></h3>
            <div class="form-grid">
                <?php foreach ($fields as $key => [$label, $type]): $v = setting($key); $wide = in_array($type, ['textarea', 'textarea-lg', 'image'], true); ?>
                    <div class="field <?= $wide ? 'span-2' : '' ?>">
                        <?php if ($type === 'image'): ?>
                            <span class="label"><?= e($label) ?></span>
                            <?php if ($v): ?><img class="current-img" src="<?= media_url($v) ?>" alt=""><?php endif; ?>
                            <label class="upload" style="padding:16px">
                                <?= icon('upload') ?><strong><?= $v ? 'Ganti foto' : 'Unggah foto' ?></strong><span class="upload__name">JPG, PNG, WEBP</span>
                                <img class="upload__preview" alt="">
                                <input type="file" name="<?= e($key) ?>" accept="image/jpeg,image/png,image/webp" data-resize>
                            </label>
                        <?php else: ?>
                            <label for="s-<?= e($key) ?>"><?= e($label) ?></label>
                            <?php if ($type === 'textarea' || $type === 'textarea-lg'): ?>
                                <textarea class="textarea" id="s-<?= e($key) ?>" name="<?= e($key) ?>" rows="<?= $type === 'textarea-lg' ? 12 : 4 ?>"><?= e($v) ?></textarea>
                            <?php else: ?>
                                <input class="input" id="s-<?= e($key) ?>" name="<?= e($key) ?>" value="<?= e($v) ?>" <?= $type === 'number' ? 'inputmode="decimal"' : '' ?>>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
    <div class="form-actions"><button class="btn" type="submit"><?= icon('save') ?> Simpan perubahan</button></div>
</form>
