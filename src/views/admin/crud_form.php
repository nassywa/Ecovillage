<?php

use function App\{e, icon, csrf_field, media_url, setting};

$key = $ent['key'];
$center = json_encode([(float) setting('map_lat', '-2.47'), (float) setting('map_lng', '111.92')]);
$val = fn ($name, $f) => array_key_exists($name, $row) ? (string) $row[$name] : (string) ($f['default'] ?? '');
?>
<p><a href="/admin/kelola/<?= e($key) ?>"><?= icon('arrow-left', 'i-sm') ?> Kembali ke <?= e($ent['title']) ?></a></p>
<form class="card" method="post" action="/admin/kelola/<?= e($key) ?>/<?= $id ? (int) $id : 'baru' ?>" enctype="multipart/form-data" data-upload-form>
    <?= csrf_field() ?>
    <div class="form-grid">
        <?php foreach ($ent['fields'] as $name => $f):
            $type = $f['type'];
            if ($type === 'hidden-number') continue;
            $v = $val($name, $f);
            $wide = in_array($type, ['textarea', 'image', 'polygon', 'latlng', 'icon', 'checkbox'], true) || $name === 'title' || $name === 'name';
            $req = !empty($f['required']) || ($type === 'select' && isset($f['default']));
            ?>
            <div class="field <?= $wide ? 'span-2' : '' ?>">
                <?php if ($type === 'checkbox'): ?>
                    <label class="checkbox"><input type="checkbox" name="<?= e($name) ?>" value="1" <?= $v ? 'checked' : '' ?>> <?= e($f['label']) ?></label>
                <?php elseif ($type === 'image'): ?>
                    <span class="label"><?= e($f['label']) ?><?= $req ? ' <span class="req">*</span>' : '' ?></span>
                    <?php if (!empty($row[$name])): ?>
                        <img class="current-img" src="<?= media_url($row[$name]) ?>" alt="">
                        <?php if (!$req): ?><label class="checkbox small"><input type="checkbox" name="remove_<?= e($name) ?>" value="1"> Hapus foto ini</label><?php endif; ?>
                    <?php endif; ?>
                    <label class="upload" style="padding:18px">
                        <?= icon('upload') ?><strong><?= !empty($row[$name]) ? 'Ganti foto' : 'Unggah foto' ?></strong>
                        <span class="upload__name">JPG, PNG, WEBP · otomatis diperkecil</span>
                        <img class="upload__preview" alt="">
                        <input type="file" name="<?= e($name) ?>" accept="image/jpeg,image/png,image/webp" data-resize <?= $req && empty($row[$name]) ? 'required' : '' ?>>
                    </label>
                <?php else: ?>
                    <label for="f-<?= e($name) ?>"><?= e($f['label']) ?><?= $req ? ' <span class="req">*</span>' : '' ?></label>
                    <?php if ($type === 'textarea'): ?>
                        <textarea class="textarea" id="f-<?= e($name) ?>" name="<?= e($name) ?>" rows="<?= (int) ($f['rows'] ?? 5) ?>" <?= $req ? 'required' : '' ?> <?= isset($f['max']) ? 'maxlength="' . (int) $f['max'] . '"' : '' ?>><?= e($v) ?></textarea>
                    <?php elseif ($type === 'select' || $type === 'relation'): ?>
                        <?php $opts = $type === 'relation' ? ($options[$name] ?? []) : $f['options']; $list = $type === 'select' && array_is_list($opts); ?>
                        <select class="select" id="f-<?= e($name) ?>" name="<?= e($name) ?>" <?= $req ? 'required' : '' ?>>
                            <?php if (!$req): ?><option value="">—</option><?php endif; ?>
                            <?php foreach ($opts as $ok => $ol): $ov = $list ? $ol : (string) $ok; ?>
                                <option value="<?= e($ov) ?>" <?= $v === $ov ? 'selected' : '' ?>><?= e($ol) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ($type === 'color'): ?>
                        <input class="input" type="color" id="f-<?= e($name) ?>" name="<?= e($name) ?>" value="<?= e($v ?: '#16803C') ?>" style="max-width:120px;padding:4px">
                    <?php elseif ($type === 'icon'): ?>
                        <div class="icon-pick" role="radiogroup" aria-label="Pilih ikon">
                            <?php foreach (\App\Crud\ICON_CHOICES as $ic): ?>
                                <label title="<?= e($ic) ?>"><input type="radio" name="<?= e($name) ?>" value="<?= e($ic) ?>" <?= $v === $ic ? 'checked' : '' ?>><span><?= icon($ic) ?></span></label>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($type === 'polygon'): ?>
                        <div class="picker-map" data-polygon-picker data-target="f-<?= e($name) ?>" data-center='<?= e($center) ?>'></div>
                        <div class="row-actions" style="justify-content:flex-start;margin-top:8px">
                            <button class="btn btn--sm btn--soft" type="button" data-poly-undo><?= icon('undo-2', 'i-sm') ?> Hapus titik terakhir</button>
                            <button class="btn btn--sm btn--ghost" type="button" data-poly-clear>Kosongkan</button>
                        </div>
                        <textarea class="textarea" id="f-<?= e($name) ?>" name="<?= e($name) ?>" rows="3" style="min-height:70px;font-family:monospace;font-size:.8rem" <?= $req ? 'required' : '' ?>><?= e($v) ?></textarea>
                    <?php elseif ($type === 'latlng'): ?>
                        <div class="picker-map" data-latlng-picker data-center='<?= e($center) ?>' id="f-<?= e($name) ?>"></div>
                        <div class="form-grid" style="margin-top:8px">
                            <input class="input" name="lat" value="<?= e($row['lat'] ?? '') ?>" placeholder="Lintang (lat)" aria-label="Lintang" required>
                            <input class="input" name="lng" value="<?= e($row['lng'] ?? '') ?>" placeholder="Bujur (lng)" aria-label="Bujur" required>
                        </div>
                    <?php else: ?>
                        <input class="input" id="f-<?= e($name) ?>" name="<?= e($name) ?>"
                               type="<?= $type === 'date' ? 'date' : ($type === 'number' ? 'number' : 'text') ?>" <?= $type === 'number' ? 'step="any"' : '' ?>
                               value="<?= e($type === 'date' ? substr($v, 0, 10) : $v) ?>" <?= $req ? 'required' : '' ?>
                               <?= isset($f['max']) ? 'maxlength="' . (int) $f['max'] . '"' : '' ?> placeholder="<?= e($f['placeholder'] ?? '') ?>">
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (!empty($f['help'])): ?><span class="help"><?= e($f['help']) ?></span><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="form-actions">
        <button class="btn" type="submit"><?= icon('save') ?> Simpan</button>
        <a class="btn btn--ghost" href="/admin/kelola/<?= e($key) ?>">Batal</a>
    </div>
</form>
