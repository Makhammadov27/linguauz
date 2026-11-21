<?php
use App\Core\View;
use App\Models\Collection;
?>
<section class="page-head">
    <div>
        <h1><?= $filters['archived'] ? 'Arxivlangan to‘plamlar' : 'So‘z to‘plamlari' ?></h1>
        <p class="muted">Topildi: <strong><?= (int) $result['total'] ?></strong> ta to‘plam</p>
    </div>
    <?php if ($user): ?>
        <div class="actions">
            <a class="btn btn-primary" href="<?= e(url('/collections/create')) ?>">+ Yangi to‘plam</a>
        </div>
    <?php endif; ?>
</section>

<form class="filters card" method="get" action="<?= e(url('/collections')) ?>" role="search" aria-label="To‘plamlarni filtrlash">
    <div class="field field-grow">
        <label for="q">Qidiruv</label>
        <input type="search" id="q" name="q" value="<?= e($filters['q']) ?>" maxlength="100" placeholder="Nomi yoki tavsifi…"
               data-live-filter="#collection-grid [data-filter-item]" autocomplete="off">
    </div>
    <div class="field">
        <label for="language_id">Til</label>
        <select id="language_id" name="language_id" data-autosubmit>
            <option value="">Barchasi</option>
            <?php foreach ($languages as $l): ?>
                <option value="<?= (int) $l['id'] ?>"<?= $filters['language_id'] === (int) $l['id'] ? ' selected' : '' ?>><?= e($l['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="level">Daraja</label>
        <select id="level" name="level" data-autosubmit>
            <option value="">Barchasi</option>
            <?php foreach (Collection::LEVELS as $lv): ?>
                <option value="<?= e($lv) ?>"<?= $filters['level'] === $lv ? ' selected' : '' ?>><?= e($lv) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="sort">Saralash</label>
        <select id="sort" name="sort" data-autosubmit>
            <?php foreach (['created' => 'Yangilari', 'title' => 'Nomi bo‘yicha', 'level' => 'Daraja bo‘yicha', 'words' => 'So‘zlar soni'] as $k => $label): ?>
                <option value="<?= e($k) ?>"<?= $filters['sort'] === $k ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="dir">Tartib</label>
        <select id="dir" name="dir" data-autosubmit>
            <option value="desc"<?= $filters['dir'] === 'desc' ? ' selected' : '' ?>>Kamayish</option>
            <option value="asc"<?= $filters['dir'] === 'asc' ? ' selected' : '' ?>>O‘sish</option>
        </select>
    </div>
    <?php if ($user): ?>
        <div class="field field-check">
            <input type="checkbox" id="mine" name="mine" value="1"<?= $filters['mine'] ? ' checked' : '' ?> data-autosubmit>
            <label for="mine">Faqat meniki</label>
        </div>
        <div class="field field-check">
            <input type="checkbox" id="archived" name="archived" value="1"<?= $filters['archived'] ? ' checked' : '' ?> data-autosubmit>
            <label for="archived">Arxiv</label>
        </div>
    <?php endif; ?>
    <div class="field field-actions">
        <button type="submit" class="btn btn-primary">Qidirish</button>
        <a class="btn btn-ghost" href="<?= e(url('/collections')) ?>">Tozalash</a>
    </div>
</form>

<?php if ($result['items']): ?>
    <p class="live-filter-status muted small" aria-live="polite" hidden></p>
    <div class="grid grid-3" id="collection-grid">
        <?php foreach ($result['items'] as $c): ?>
            <?php View::partial('collection_card', ['c' => $c]); ?>
        <?php endforeach; ?>
    </div>
    <?php View::partial('pagination', ['page' => $result['page'], 'pages' => $result['pages']]); ?>
<?php else: ?>
    <div class="empty card">
        <p><strong>To‘plam topilmadi.</strong></p>
        <p class="muted"><?= $filters['archived'] ? 'Arxivda to‘plamlar yo‘q.' : 'Filtrlarni o‘zgartirib ko‘ring.' ?></p>
    </div>
<?php endif; ?>
