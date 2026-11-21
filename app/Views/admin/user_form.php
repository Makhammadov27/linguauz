<?php
use App\Core\View;
use App\Models\User;

View::partial('../admin/_nav');
$isEdit = !empty($u['id']);
?>
<section class="page-head"><h1><?= $isEdit ? 'Foydalanuvchini tahrirlash' : 'Yangi foydalanuvchi' ?></h1></section>

<form method="post" action="<?= e($isEdit ? url('/admin/users/' . $u['id']) : url('/admin/users')) ?>" class="form card form-narrow" data-validate novalidate>
    <?= csrf_field() ?>
    <div class="field<?= isset($errors['name']) ? ' has-error' : '' ?>">
        <label for="name">Ism</label>
        <input type="text" id="name" name="name" required minlength="2" maxlength="100" value="<?= e($u['name'] ?? '') ?>">
        <?php View::partial('field_error', ['errors' => $errors, 'field' => 'name']); ?>
    </div>
    <div class="field<?= isset($errors['email']) ? ' has-error' : '' ?>">
        <label for="email">Elektron pochta</label>
        <input type="email" id="email" name="email" required maxlength="150" value="<?= e($u['email'] ?? '') ?>">
        <?php View::partial('field_error', ['errors' => $errors, 'field' => 'email']); ?>
    </div>
    <div class="field<?= isset($errors['role']) ? ' has-error' : '' ?>">
        <label for="role">Rol</label>
        <select id="role" name="role" required>
            <?php foreach (User::ROLES as $k => $label): ?>
                <option value="<?= e($k) ?>"<?= ($u['role'] ?? '') === $k ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <?php View::partial('field_error', ['errors' => $errors, 'field' => 'role']); ?>
    </div>
    <div class="field<?= isset($errors['password']) ? ' has-error' : '' ?>">
        <label for="password"><?= $isEdit ? 'Yangi parol (o‘zgartirmaslik uchun bo‘sh qoldiring)' : 'Parol' ?></label>
        <input type="password" id="password" name="password" <?= $isEdit ? '' : 'required ' ?>minlength="8" maxlength="72" autocomplete="new-password" data-rule="letter-digit">
        <?php View::partial('field_error', ['errors' => $errors, 'field' => 'password']); ?>
    </div>
    <div class="actions">
        <button type="submit" class="btn btn-primary">Saqlash</button>
        <a class="btn btn-ghost" href="<?= e(url('/admin/users')) ?>">Bekor qilish</a>
    </div>
</form>
