<nav class="admin-nav" aria-label="Boshqaruv bo‘limlari">
    <a href="<?= e(url('/admin')) ?>"<?= current_path() === '/admin' ? ' aria-current="page"' : '' ?>>Umumiy</a>
    <a href="<?= e(url('/admin/users')) ?>"<?= nav_active('/admin/users') ?>>Foydalanuvchilar</a>
    <a href="<?= e(url('/admin/languages')) ?>"<?= nav_active('/admin/languages') ?>>Tillar</a>
    <a href="<?= e(url('/admin/messages')) ?>"<?= nav_active('/admin/messages') ?>>Aloqa xabarlari</a>
</nav>
