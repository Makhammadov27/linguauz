<?php
use App\Core\View;
use App\Models\Exercise;

$isEdit = !empty($ex['id']);
$v = static fn(string $k): string => (string) ($ex[$k] ?? '');
?>
<section class="page-head"><h1><?= $isEdit ? 'Mashqni tahrirlash' : 'Yangi mashq' ?></h1></section>

<form method="post" action="<?= e($isEdit ? url('/exercises/' . $ex['id']) : url('/exercises')) ?>" class="form card form-narrow" data-validate novalidate>
    <?= csrf_field() ?>
    <?php if ($errors): ?>
        <div class="alert alert-error" role="alert">Formada xatolar bor. Belgilangan maydonlarni tekshiring.</div>
    <?php endif; ?>
    <div class="field<?= isset($errors['title']) ? ' has-error' : '' ?>">
        <label for="title">Nomi <span class="req" aria-hidden="true">*</span></label>
        <input type="text" id="title" name="title" required minlength="3" maxlength="150" value="<?= e($v('title')) ?>">
        <?php View::partial('field_error', ['errors' => $errors, 'field' => 'title']); ?>
    </div>
    <div class="field<?= isset($errors['collection_id']) ? ' has-error' : '' ?>">
        <label for="collection_id">To‘plam <span class="req" aria-hidden="true">*</span></label>
        <select id="collection_id" name="collection_id" required>
            <option value="">— tanlang —</option>
            <?php foreach ($collections as $c): ?>
                <option value="<?= (int) $c['id'] ?>"<?= (int) $v('collection_id') === (int) $c['id'] ? ' selected' : '' ?>><?= e($c['title']) ?> (<?= e($c['language_name']) ?>)</option>
            <?php endforeach; ?>
        </select>
        <?php View::partial('field_error', ['errors' => $errors, 'field' => 'collection_id']); ?>
    </div>
    <div class="grid grid-2 grid-tight">
        <fieldset class="field<?= isset($errors['type']) ? ' has-error' : '' ?>">
            <legend>Turi <span class="req" aria-hidden="true">*</span></legend>
            <?php foreach (Exercise::TYPES as $k => $label): ?>
                <label class="radio"><input type="radio" name="type" value="<?= e($k) ?>" required<?= $v('type') === $k ? ' checked' : '' ?>> <?= e($label) ?></label>
            <?php endforeach; ?>
            <?php View::partial('field_error', ['errors' => $errors, 'field' => 'type']); ?>
        </fieldset>
        <fieldset class="field<?= isset($errors['direction']) ? ' has-error' : '' ?>">
            <legend>Yo‘nalish <span class="req" aria-hidden="true">*</span></legend>
            <?php foreach (Exercise::DIRECTIONS as $k => $label): ?>
                <label class="radio"><input type="radio" name="direction" value="<?= e($k) ?>" required<?= $v('direction') === $k ? ' checked' : '' ?>> <?= e($label) ?></label>
            <?php endforeach; ?>
            <?php View::partial('field_error', ['errors' => $errors, 'field' => 'direction']); ?>
        </fieldset>
    </div>
    <div class="field<?= isset($errors['question_count']) ? ' has-error' : '' ?>">
        <label for="question_count">Savollar soni (3–30) <span class="req" aria-hidden="true">*</span></label>
        <input type="number" id="question_count" name="question_count" required min="3" max="30" step="1" value="<?= e($v('question_count')) ?>">
        <?php View::partial('field_error', ['errors' => $errors, 'field' => 'question_count']); ?>
    </div>
    <div class="actions">
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Saqlash' : 'Yaratish' ?></button>
        <a class="btn btn-ghost" href="<?= e($isEdit ? url('/exercises/' . $ex['id']) : url('/exercises')) ?>">Bekor qilish</a>
    </div>
</form>
