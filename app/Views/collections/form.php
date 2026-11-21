<?php
use App\Core\View;
use App\Models\Collection;

$isEdit = !empty($c['id']);
$v = static fn(string $k): string => (string) ($c[$k] ?? '');
?>
<section class="page-head">
    <h1><?= $isEdit ? 'To‘plamni tahrirlash' : 'Yangi to‘plam' ?></h1>
</section>

<form method="post" action="<?= e($isEdit ? url('/collections/' . $c['id']) : url('/collections')) ?>" class="form card form-narrow" data-validate novalidate>
    <?= csrf_field() ?>
    <?php if ($errors): ?>
        <div class="alert alert-error" role="alert">Formada xatolar bor. Belgilangan maydonlarni tekshiring.</div>
    <?php endif; ?>

    <div class="field<?= isset($errors['title']) ? ' has-error' : '' ?>">
        <label for="title">Nomi <span class="req" aria-hidden="true">*</span></label>
        <input type="text" id="title" name="title" required minlength="3" maxlength="150" value="<?= e($v('title')) ?>">
        <?php View::partial('field_error', ['errors' => $errors, 'field' => 'title']); ?>
    </div>
    <div class="grid grid-2 grid-tight">
        <div class="field<?= isset($errors['language_id']) ? ' has-error' : '' ?>">
            <label for="language_id">Til <span class="req" aria-hidden="true">*</span></label>
            <select id="language_id" name="language_id" required>
                <option value="">— tanlang —</option>
                <?php foreach ($languages as $l): ?>
                    <option value="<?= (int) $l['id'] ?>"<?= (int) $v('language_id') === (int) $l['id'] ? ' selected' : '' ?>><?= e($l['name']) ?> (<?= e($l['native_name']) ?>)</option>
                <?php endforeach; ?>
            </select>
            <?php View::partial('field_error', ['errors' => $errors, 'field' => 'language_id']); ?>
        </div>
        <div class="field<?= isset($errors['level']) ? ' has-error' : '' ?>">
            <label for="level">Daraja (CEFR) <span class="req" aria-hidden="true">*</span></label>
            <select id="level" name="level" required>
                <?php foreach (Collection::LEVELS as $lv): ?>
                    <option value="<?= e($lv) ?>"<?= $v('level') === $lv ? ' selected' : '' ?>><?= e($lv) ?></option>
                <?php endforeach; ?>
            </select>
            <?php View::partial('field_error', ['errors' => $errors, 'field' => 'level']); ?>
        </div>
    </div>
    <div class="field<?= isset($errors['description']) ? ' has-error' : '' ?>">
        <label for="description">Tavsif</label>
        <textarea id="description" name="description" rows="4" maxlength="2000" data-counter><?= e($v('description')) ?></textarea>
        <?php View::partial('field_error', ['errors' => $errors, 'field' => 'description']); ?>
    </div>
    <?php if ($canPublish): ?>
        <div class="field field-check">
            <input type="checkbox" id="is_public" name="is_public" value="1"<?= (int) $v('is_public') === 1 ? ' checked' : '' ?>>
            <label for="is_public">Hammaga ochiq (ommaviy to‘plam)</label>
        </div>
    <?php else: ?>
        <p class="hint">O‘quvchi yaratgan to‘plam shaxsiy bo‘ladi va faqat sizga ko‘rinadi.</p>
    <?php endif; ?>

    <div class="actions">
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Saqlash' : 'Yaratish' ?></button>
        <a class="btn btn-ghost" href="<?= e($isEdit ? url('/collections/' . $c['id']) : url('/collections')) ?>">Bekor qilish</a>
    </div>
</form>
