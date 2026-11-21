<section class="auth-box card">
    <h1>Tizimga kirish</h1>
    <?php if (!empty($errors['form'])): ?>
        <div class="alert alert-error" role="alert"><?= e($errors['form']) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= e(url('/login')) ?>" class="form" data-validate novalidate>
        <?= csrf_field() ?>
        <div class="field">
            <label for="email">Elektron pochta</label>
            <input type="email" id="email" name="email" required maxlength="150" autocomplete="username"
                   value="<?= e($old['email'] ?? '') ?>" autofocus>
        </div>
        <div class="field">
            <label for="password">Parol</label>
            <input type="password" id="password" name="password" required maxlength="72" autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Kirish</button>
    </form>
    <p class="muted center">Hisobingiz yo‘qmi? <a href="<?= e(url('/register')) ?>">Ro‘yxatdan o‘ting</a></p>
</section>
