<section class="error-page">
    <p class="error-code"><?= (int) $code ?></p>
    <h1><?= e($title) ?></h1>
    <?php if ($message !== ''): ?>
        <p class="lead"><?= e($message) ?></p>
    <?php endif; ?>
    <div class="actions">
        <a class="btn btn-primary" href="<?= e(url('/')) ?>">Bosh sahifaga</a>
        <?php if ((int) $code === 403 && !current_user()): ?>
            <a class="btn btn-secondary" href="<?= e(url('/login')) ?>">Tizimga kirish</a>
        <?php endif; ?>
    </div>
</section>
