<?php \App\Core\View::partial('../admin/_nav'); ?>
<section class="page-head"><h1>Boshqaruv paneli</h1></section>
<section class="stats">
    <div class="stat"><strong><?= (int) $stats['users'] ?></strong><span>faol foydalanuvchi</span></div>
    <div class="stat"><strong><?= (int) $stats['collections'] ?></strong><span>ommaviy to‘plam</span></div>
    <div class="stat"><strong><?= (int) $stats['words'] ?></strong><span>ommaviy so‘z</span></div>
    <div class="stat stat-accent"><strong><?= (int) $stats['unread'] ?></strong><span>o‘qilmagan xabar</span></div>
</section>
<div class="grid grid-3 section">
    <a class="card card-link" href="<?= e(url('/admin/users')) ?>"><h2 class="h3">Foydalanuvchilar va rollar</h2><p class="muted">Hisob yaratish, rolni o‘zgartirish, bloklash.</p></a>
    <a class="card card-link" href="<?= e(url('/admin/languages')) ?>"><h2 class="h3">Tillar ro‘yxati</h2><p class="muted">O‘rganiladigan tillar va talaffuz kodlari.</p></a>
    <a class="card card-link" href="<?= e(url('/admin/messages')) ?>"><h2 class="h3">Aloqa xabarlari</h2><p class="muted">Xabarlar va pochtaga yuborish holati.</p></a>
</div>
