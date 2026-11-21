<?php
use App\Core\View;
use App\Models\ContactMessage;

View::partial('../admin/_nav');
?>
<section class="page-head">
    <div><h1>Aloqa xabarlari</h1><p class="muted">Jami: <?= (int) $result['total'] ?> · qabul qiluvchi manzil server sozlamasida: <code><?= e(config('mail.to_email')) ?></code></p></div>
    <form method="get" action="<?= e(url('/admin/messages')) ?>" class="inline-filter">
        <label for="status" class="sr-only">Pochta holati</label>
        <select id="status" name="status" data-autosubmit>
            <option value="">Barcha holatlar</option>
            <?php foreach (ContactMessage::STATUSES as $k => $label): ?>
                <option value="<?= e($k) ?>"<?= $status === $k ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <noscript><button type="submit" class="btn btn-secondary btn-sm">Filtr</button></noscript>
    </form>
</section>

<?php if ($result['items']): ?>
    <ul class="messages plain">
        <?php foreach ($result['items'] as $m): ?>
            <li class="card message<?= (int) $m['is_read'] ? '' : ' is-unread' ?>">
                <header class="message-head">
                    <div>
                        <strong><?= e($m['name']) ?></strong> &lt;<?= e($m['email']) ?>&gt;
                        <span class="muted small">· <?= e(fmt_date($m['created_at'], 'd.m.Y H:i')) ?> · IP <?= e($m['ip']) ?></span>
                    </div>
                    <span class="badge mail-<?= e($m['mail_status']) ?>">Pochta: <?= e(ContactMessage::STATUSES[$m['mail_status']]) ?></span>
                </header>
                <p class="message-body"><?= nl2br(e($m['message'])) ?></p>
                <?php if ($m['mail_error']): ?><p class="small text-danger">SMTP xatosi: <?= e($m['mail_error']) ?></p><?php endif; ?>
                <div class="actions">
                    <?php if ($m['mail_status'] !== 'sent'): ?>
                        <form method="post" action="<?= e(url('/admin/messages/' . $m['id'] . '/resend')) ?>" class="inline-form">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-secondary btn-sm">Pochtaga qayta yuborish</button>
                        </form>
                    <?php endif; ?>
                    <form method="post" action="<?= e(url('/admin/messages/' . $m['id'] . '/delete')) ?>" class="inline-form" data-confirm="Xabarni o‘chirasizmi?">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger btn-sm">O‘chirish</button>
                    </form>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php View::partial('pagination', ['page' => $result['page'], 'pages' => $result['pages']]); ?>
<?php else: ?>
    <p class="empty card">Xabarlar yo‘q.</p>
<?php endif; ?>
