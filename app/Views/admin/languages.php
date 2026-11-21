<?php
use App\Core\View;

View::partial('../admin/_nav');
?>
<section class="page-head"><h1>Tillar</h1></section>

<div class="table-wrap">
    <table class="table">
        <thead><tr><th scope="col">Kod</th><th scope="col">Nomi</th><th scope="col">O‘z tilida</th><th scope="col">Talaffuz</th><th scope="col">To‘plamlar</th><th scope="col"><span class="sr-only">Amallar</span></th></tr></thead>
        <tbody>
        <?php foreach ($languages as $l): ?>
            <tr>
                <td colspan="4">
                    <form method="post" action="<?= e(url('/admin/languages/' . $l['id'])) ?>" class="row-form">
                        <?= csrf_field() ?>
                        <label class="sr-only" for="code-<?= (int) $l['id'] ?>">Kod</label>
                        <input id="code-<?= (int) $l['id'] ?>" name="code" value="<?= e($l['code']) ?>" required maxlength="3" size="3">
                        <label class="sr-only" for="name-<?= (int) $l['id'] ?>">Nomi</label>
                        <input id="name-<?= (int) $l['id'] ?>" name="name" value="<?= e($l['name']) ?>" required maxlength="60">
                        <label class="sr-only" for="native-<?= (int) $l['id'] ?>">O‘z tilida</label>
                        <input id="native-<?= (int) $l['id'] ?>" name="native_name" value="<?= e($l['native_name']) ?>" required maxlength="60">
                        <label class="sr-only" for="speech-<?= (int) $l['id'] ?>">Talaffuz kodi</label>
                        <input id="speech-<?= (int) $l['id'] ?>" name="speech_code" value="<?= e($l['speech_code']) ?>" required maxlength="10" size="6">
                        <button type="submit" class="btn btn-secondary btn-sm">Saqlash</button>
                    </form>
                </td>
                <td><?= (int) $l['collections_count'] ?></td>
                <td>
                    <form method="post" action="<?= e(url('/admin/languages/' . $l['id'] . '/delete')) ?>" data-confirm="«<?= e($l['name']) ?>» tilini o‘chirasizmi?">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger btn-sm"<?= (int) $l['collections_count'] > 0 ? ' disabled title="Tilda to‘plamlar bor"' : '' ?>>O‘chirish</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<section class="card form-narrow section">
    <h2 class="h3">Yangi til qo‘shish</h2>
    <form method="post" action="<?= e(url('/admin/languages')) ?>" class="form" data-validate novalidate>
        <?= csrf_field() ?>
        <div class="grid grid-2 grid-tight">
            <div class="field<?= isset($errors['code']) ? ' has-error' : '' ?>">
                <label for="code">ISO kod</label>
                <input id="code" name="code" required minlength="2" maxlength="3" placeholder="es" value="<?= e($old['code'] ?? '') ?>">
                <?php View::partial('field_error', ['errors' => $errors, 'field' => 'code']); ?>
            </div>
            <div class="field<?= isset($errors['speech_code']) ? ' has-error' : '' ?>">
                <label for="speech_code">Talaffuz kodi</label>
                <input id="speech_code" name="speech_code" required maxlength="10" placeholder="es-ES" value="<?= e($old['speech_code'] ?? '') ?>">
                <?php View::partial('field_error', ['errors' => $errors, 'field' => 'speech_code']); ?>
            </div>
            <div class="field<?= isset($errors['name']) ? ' has-error' : '' ?>">
                <label for="name">Nomi (o‘zbekcha)</label>
                <input id="name" name="name" required minlength="2" maxlength="60" placeholder="Ispan tili" value="<?= e($old['name'] ?? '') ?>">
                <?php View::partial('field_error', ['errors' => $errors, 'field' => 'name']); ?>
            </div>
            <div class="field<?= isset($errors['native_name']) ? ' has-error' : '' ?>">
                <label for="native_name">O‘z tilidagi nomi</label>
                <input id="native_name" name="native_name" required minlength="2" maxlength="60" placeholder="Español" value="<?= e($old['native_name'] ?? '') ?>">
                <?php View::partial('field_error', ['errors' => $errors, 'field' => 'native_name']); ?>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Qo‘shish</button>
    </form>
</section>
