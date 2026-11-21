<?php
use App\Core\View;
use App\Models\Exercise;
?>
<section class="page-head">
    <div>
        <h1>Mashqlar</h1>
        <p class="muted">Topildi: <strong><?= (int) $result['total'] ?></strong> ta mashq</p>
    </div>
    <?php if ($user): ?>
        <div class="actions"><a class="btn btn-primary" href="<?= e(url('/exercises/create')) ?>">+ Yangi mashq</a></div>
    <?php endif; ?>
</section>

<form class="filters card" method="get" action="<?= e(url('/exercises')) ?>" role="search" aria-label="Mashqlarni filtrlash">
    <div class="field field-grow">
        <label for="q">Qidiruv</label>
        <input type="search" id="q" name="q" value="<?= e($filters['q']) ?>" maxlength="100" placeholder="Mashq yoki to‘plam nomi…"
               data-live-filter="#exercise-grid [data-filter-item]" autocomplete="off">
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
        <label for="type">Turi</label>
        <select id="type" name="type" data-autosubmit>
            <option value="">Barchasi</option>
            <?php foreach (Exercise::TYPES as $k => $label): ?>
                <option value="<?= e($k) ?>"<?= $filters['type'] === $k ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="sort">Saralash</label>
        <select id="sort" name="sort" data-autosubmit>
            <?php foreach (['created' => 'Yangilari', 'title' => 'Nomi', 'questions' => 'Savollar soni'] as $k => $label): ?>
                <option value="<?= e($k) ?>"<?= $filters['sort'] === $k ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <input type="hidden" name="dir" value="<?= $filters['sort'] === 'title' ? 'asc' : 'desc' ?>">
    <div class="field field-actions">
        <button type="submit" class="btn btn-primary">Qidirish</button>
        <a class="btn btn-ghost" href="<?= e(url('/exercises')) ?>">Tozalash</a>
    </div>
</form>

<?php if ($result['items']): ?>
    <p class="live-filter-status muted small" aria-live="polite" hidden></p>
    <div class="grid grid-3" id="exercise-grid">
        <?php foreach ($result['items'] as $ex): ?>
            <article class="card exercise-card" data-filter-item>
                <div class="card-tags">
                    <span class="badge badge-level"><?= e($ex['level']) ?></span>
                    <span class="badge"><?= e($ex['language_name']) ?></span>
                    <span class="badge badge-type"><?= e(Exercise::TYPES[$ex['type']]) ?></span>
                </div>
                <h2 class="card-title h3"><a href="<?= e(url('/exercises/' . $ex['id'])) ?>"><?= e($ex['title']) ?></a></h2>
                <p class="muted small"><?= e($ex['collection_title']) ?> · <?= (int) min($ex['question_count'], $ex['words_count']) ?> savol</p>
                <p class="small"><?= e(Exercise::DIRECTIONS[$ex['direction']]) ?></p>
                <?php if (isset($ex['best_percent']) && $ex['best_percent'] !== null): ?>
                    <p class="small">Eng yaxshi natijangiz: <strong><?= e(rtrim(rtrim((string) $ex['best_percent'], '0'), '.')) ?>%</strong></p>
                <?php endif; ?>
                <a class="btn btn-secondary btn-sm" href="<?= e(url('/exercises/' . $ex['id'] . '/play')) ?>">Boshlash</a>
            </article>
        <?php endforeach; ?>
    </div>
    <?php View::partial('pagination', ['page' => $result['page'], 'pages' => $result['pages']]); ?>
<?php else: ?>
    <div class="empty card"><p><strong>Mashq topilmadi.</strong></p><p class="muted">Filtrlarni o‘zgartirib ko‘ring.</p></div>
<?php endif; ?>
