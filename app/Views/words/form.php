<?php
use App\Core\View;
use App\Models\Word;

$isEdit = !empty($word['id']);
$action = $isEdit ? url('/words/' . $word['id']) : url('/words');
$v = static fn(string $k): string => (string) ($word[$k] ?? '');
?>
<section class="page-head">
    <h1><?= $isEdit ? 'So‘zni tahrirlash' : 'Yangi so‘z qo‘shish' ?></h1>
</section>

<form method="post" action="<?= e($action) ?>" class="form card form-narrow" data-validate novalidate>
    <?= csrf_field() ?>
    <?php if ($errors): ?>
        <div class="alert alert-error" role="alert">Formada xatolar bor. Belgilangan maydonlarni tekshiring.</div>
    <?php endif; ?>

    <div class="field<?= isset($errors['collection_id']) ? ' has-error' : '' ?>">
        <label for="collection_id">To‘plam <span class="req" aria-hidden="true">*</span></label>
        <select id="collection_id" name="collection_id" required>
            <option value="">— tanlang —</option>
            <?php foreach ($collections as $c): ?>
                <option value="<?= (int) $c['id'] ?>"<?= (int) $v('collection_id') === (int) $c['id'] ? ' selected' : '' ?>>
                    <?= e($c['title']) ?> (<?= e($c['language_name']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <?php View::partial('field_error', ['errors' => $errors, 'field' => 'collection_id']); ?>
    </div>

    <div class="grid grid-2 grid-tight">
        <div class="field<?= isset($errors['term']) ? ' has-error' : '' ?>">
            <label for="term">So‘z yoki ibora <span class="req" aria-hidden="true">*</span></label>
            <input type="text" id="term" name="term" required maxlength="120" value="<?= e($v('term')) ?>">
            <?php View::partial('field_error', ['errors' => $errors, 'field' => 'term']); ?>
        </div>
        <div class="field<?= isset($errors['translation']) ? ' has-error' : '' ?>">
            <label for="translation">O‘zbekcha tarjima <span class="req" aria-hidden="true">*</span></label>
            <input type="text" id="translation" name="translation" required maxlength="200" value="<?= e($v('translation')) ?>"
                   aria-describedby="translation-hint">
            <p class="hint" id="translation-hint">Bir nechta ma’noni vergul bilan ajrating: «yuk, bagaj».</p>
            <?php View::partial('field_error', ['errors' => $errors, 'field' => 'translation']); ?>
        </div>
        <div class="field<?= isset($errors['transcription']) ? ' has-error' : '' ?>">
            <label for="transcription">Transkripsiya</label>
            <input type="text" id="transcription" name="transcription" maxlength="120" value="<?= e($v('transcription')) ?>" placeholder="/həˈləʊ/">
            <?php View::partial('field_error', ['errors' => $errors, 'field' => 'transcription']); ?>
        </div>
        <div class="field<?= isset($errors['part_of_speech']) ? ' has-error' : '' ?>">
            <label for="part_of_speech">So‘z turkumi <span class="req" aria-hidden="true">*</span></label>
            <select id="part_of_speech" name="part_of_speech" required>
                <?php foreach (Word::POS as $k => $label): ?>
                    <option value="<?= e($k) ?>"<?= $v('part_of_speech') === $k ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <?php View::partial('field_error', ['errors' => $errors, 'field' => 'part_of_speech']); ?>
        </div>
    </div>

    <div class="field<?= isset($errors['example']) ? ' has-error' : '' ?>">
        <label for="example">Misol gap</label>
        <textarea id="example" name="example" rows="2" maxlength="500" data-counter><?= e($v('example')) ?></textarea>
        <?php View::partial('field_error', ['errors' => $errors, 'field' => 'example']); ?>
    </div>
    <div class="field<?= isset($errors['example_translation']) ? ' has-error' : '' ?>">
        <label for="example_translation">Misol tarjimasi</label>
        <textarea id="example_translation" name="example_translation" rows="2" maxlength="500" data-counter
                  data-requires="example" data-requires-message="Misol tarjimasini kiritish uchun avval misolni yozing."><?= e($v('example_translation')) ?></textarea>
        <?php View::partial('field_error', ['errors' => $errors, 'field' => 'example_translation']); ?>
    </div>

    <div class="actions">
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Saqlash' : 'Qo‘shish' ?></button>
        <a class="btn btn-ghost" href="<?= e($isEdit ? url('/words/' . $word['id']) : url('/words')) ?>">Bekor qilish</a>
    </div>
</form>
