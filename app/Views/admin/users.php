<?php
use App\Core\View;
use App\Models\User;

View::partial('../admin/_nav');
$me = current_user();
?>
<section class="page-head">
    <div><h1>Foydalanuvchilar</h1><p class="muted">Jami: <?= (int) $result['total'] ?></p></div>
    <div class="actions"><a class="btn btn-primary" href="<?= e(url('/admin/users/create')) ?>">+ Yangi foydalanuvchi</a></div>
</section>

<form class="filters card" method="get" action="<?= e(url('/admin/users')) ?>" role="search">
    <div class="field field-grow">
        <label for="q">Ism yoki pochta</label>
        <input type="search" id="q" name="q" value="<?= e($q) ?>" maxlength="100">
    </div>
    <div class="field">
        <label for="role">Rol</label>
        <select id="role" name="role" data-autosubmit>
            <option value="">Barchasi</option>
            <?php foreach (User::ROLES as $k => $label): ?>
                <option value="<?= e($k) ?>"<?= $role === $k ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field field-actions"><button type="submit" class="btn btn-primary">Qidirish</button></div>
</form>

<div class="table-wrap">
    <table class="table">
        <thead><tr><th scope="col">#</th><th scope="col">Ism</th><th scope="col">Pochta</th><th scope="col">Rol</th><th scope="col" class="hide-sm">To‘plamlar</th><th scope="col">Holat</th><th scope="col"><span class="sr-only">Amallar</span></th></tr></thead>
        <tbody>
        <?php foreach ($result['items'] as $u): ?>
            <tr>
                <td><?= (int) $u['id'] ?></td>
                <td><?= e($u['name']) ?></td>
                <td class="small"><?= e($u['email']) ?></td>
                <td><span class="badge role-<?= e($u['role']) ?>"><?= e($u['role_name']) ?></span></td>
                <td class="hide-sm"><?= (int) $u['collections_count'] ?></td>
                <td><?= (int) $u['is_active'] ? '<span class="badge state-mastered">Faol</span>' : '<span class="badge state-learning">Bloklangan</span>' ?></td>
                <td class="nowrap">
                    <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/users/' . $u['id'] . '/edit')) ?>">Tahrirlash</a>
                    <?php if ((int) $u['id'] !== (int) $me['id']): ?>
                        <form method="post" action="<?= e(url('/admin/users/' . $u['id'] . '/toggle')) ?>" class="inline-form"
                              data-confirm="<?= (int) $u['is_active'] ? 'Foydalanuvchini bloklaysizmi?' : 'Foydalanuvchini faollashtirasizmi?' ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm <?= (int) $u['is_active'] ? 'btn-danger' : 'btn-secondary' ?>"><?= (int) $u['is_active'] ? 'Bloklash' : 'Faollashtirish' ?></button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php View::partial('pagination', ['page' => $result['page'], 'pages' => $result['pages']]); ?>
