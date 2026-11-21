<?php use App\Core\View; ?>
<div class="grid grid-2 contact">
    <section>
        <h1>Aloqa</h1>
        <p class="lead">Yangi til yoki to‘plam taklif qilmoqchimisiz, xatolik topdingizmi? Bizga yozing.</p>
        <ul class="contact-info">
            <li><strong>Manzil:</strong> Andijon sh., o‘quv loyihasi</li>
            <li><strong>Ish vaqti:</strong> Dushanba–Juma, 9:00–18:00</li>
            <li><strong>Javob muddati:</strong> 1–2 ish kuni</li>
        </ul>
    </section>
    <section class="card">
        <?php if (!empty($errors['form'])): ?>
            <div class="alert alert-error" role="alert"><?= e($errors['form']) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= e(url('/contact')) ?>" class="form" data-validate novalidate>
            <?= csrf_field() ?>
            <div class="field<?= isset($errors['name']) ? ' has-error' : '' ?>">
                <label for="name">Ismingiz <span class="req" aria-hidden="true">*</span></label>
                <input type="text" id="name" name="name" required minlength="2" maxlength="100" value="<?= e($old['name']) ?>" autocomplete="name">
                <?php View::partial('field_error', ['errors' => $errors, 'field' => 'name']); ?>
            </div>
            <div class="field<?= isset($errors['email']) ? ' has-error' : '' ?>">
                <label for="email">Elektron pochta <span class="req" aria-hidden="true">*</span></label>
                <input type="email" id="email" name="email" required maxlength="150" value="<?= e($old['email']) ?>" autocomplete="email">
                <?php View::partial('field_error', ['errors' => $errors, 'field' => 'email']); ?>
            </div>
            <div class="field<?= isset($errors['message']) ? ' has-error' : '' ?>">
                <label for="message">Xabar <span class="req" aria-hidden="true">*</span></label>
                <textarea id="message" name="message" rows="6" required minlength="10" maxlength="3000" data-counter><?= e($old['message']) ?></textarea>
                <?php View::partial('field_error', ['errors' => $errors, 'field' => 'message']); ?>
            </div>
            <div class="hp-field" aria-hidden="true">
                <label for="website">Veb-sayt</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>
            <button type="submit" class="btn btn-primary">Yuborish</button>
        </form>
    </section>
</div>
