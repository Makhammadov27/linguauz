<?php use App\Core\View; ?>
<section class="auth-box card">
    <h1>Ro‘yxatdan o‘tish</h1>
    <p class="muted">Hisob yaratilgach siz «O‘quvchi» roliga ega bo‘lasiz.</p>
    <form method="post" action="<?= e(url('/register')) ?>" class="form" data-validate novalidate>
        <?= csrf_field() ?>
        <div class="field<?= isset($errors['name']) ? ' has-error' : '' ?>">
            <label for="name">Ism</label>
            <input type="text" id="name" name="name" required minlength="2" maxlength="100" autocomplete="name"
                   value="<?= e($old['name'] ?? '') ?>">
            <?php View::partial('field_error', ['errors' => $errors, 'field' => 'name']); ?>
        </div>
        <div class="field<?= isset($errors['email']) ? ' has-error' : '' ?>">
            <label for="email">Elektron pochta</label>
            <input type="email" id="email" name="email" required maxlength="150" autocomplete="email"
                   value="<?= e($old['email'] ?? '') ?>">
            <?php View::partial('field_error', ['errors' => $errors, 'field' => 'email']); ?>
        </div>
        <div class="field<?= isset($errors['password']) ? ' has-error' : '' ?>">
            <label for="password">Parol</label>
            <input type="password" id="password" name="password" required minlength="8" maxlength="72"
                   autocomplete="new-password" data-rule="letter-digit" aria-describedby="password-hint">
            <p class="hint" id="password-hint">Kamida 8 belgi, harf va raqam bo‘lishi shart.</p>
            <?php View::partial('field_error', ['errors' => $errors, 'field' => 'password']); ?>
        </div>
        <div class="field<?= isset($errors['password_confirm']) ? ' has-error' : '' ?>">
            <label for="password_confirm">Parolni tasdiqlang</label>
            <input type="password" id="password_confirm" name="password_confirm" required maxlength="72"
                   autocomplete="new-password" data-match="password" data-match-message="Parollar bir-biriga mos kelmadi.">
            <?php View::partial('field_error', ['errors' => $errors, 'field' => 'password_confirm']); ?>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Hisob yaratish</button>
    </form>
    <p class="muted center">Hisobingiz bormi? <a href="<?= e(url('/login')) ?>">Kirish</a></p>
</section>
