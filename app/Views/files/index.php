<?php
use App\Core\View;
use App\Models\File;
?>
<section class="page-head">
    <div>
        <h1>O‘quv materiallari</h1>
        <p class="muted">To‘plamlarga biriktirilgan rasmlar, PDF qo‘llanmalar va video darslar. Topildi: <strong><?= (int) $result['total'] ?></strong></p>
    </div>
</section>

<form class="filters card" method="get" action="<?= e(url('/files')) ?>" role="search" aria-label="Materiallarni qidirish">
    <div class="field field-grow">
        <label for="q">Fayl nomi yoki tavsifi</label>
        <input type="search" id="q" name="q" value="<?= e($q) ?>" maxlength="100" placeholder="Masalan: grammatika…">
    </div>
    <div class="field">
        <label for="category">Turi</label>
        <select id="category" name="category" data-autosubmit>
            <option value="">Barchasi</option>
            <?php foreach (File::CATEGORIES as $k => $label): ?>
                <option value="<?= e($k) ?>"<?= $category === $k ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field field-actions">
        <button type="submit" class="btn btn-primary">Qidirish</button>
        <a class="btn btn-ghost" href="<?= e(url('/files')) ?>">Tozalash</a>
    </div>
</form>

<?php if ($result['items']): ?>
    <div class="grid grid-3">
        <?php foreach ($result['items'] as $f): ?>
            <article class="card file-card">
                <div class="file-preview">
                    <?php if ($f['category'] === 'image'): ?>
                        <img src="<?= e(url('/files/' . $f['id'])) ?>" alt="<?= e($f['description'] ?: $f['original_name']) ?>" loading="lazy">
                    <?php elseif ($f['category'] === 'video'): ?>
                        <video controls preload="none" src="<?= e(url('/files/' . $f['id'])) ?>"></video>
                    <?php else: ?>
                        <span class="file-icon file-icon-lg" aria-hidden="true">PDF</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h2 class="h4 card-title"><a href="<?= e(url('/files/' . $f['id'])) ?>" target="_blank" rel="noopener"><?= e($f['original_name']) ?></a></h2>
                    <?php if ($f['description']): ?><p class="small"><?= e($f['description']) ?></p><?php endif; ?>
                    <p class="muted small"><?= e(File::CATEGORIES[$f['category']]) ?> · <?= e(human_size((int) $f['size_bytes'])) ?> ·
                        <a href="<?= e(url('/collections/' . $f['collection_id'])) ?>"><?= e($f['collection_title']) ?></a></p>
                    <a class="btn btn-ghost btn-sm" href="<?= e(url('/files/' . $f['id'], ['download' => 1])) ?>">Yuklab olish</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <?php View::partial('pagination', ['page' => $result['page'], 'pages' => $result['pages']]); ?>
<?php else: ?>
    <div class="empty card">
        <p><strong>Material topilmadi.</strong></p>
        <p class="muted">Fayllar to‘plam sahifasida egasi tomonidan yuklanadi.</p>
    </div>
<?php endif; ?>
