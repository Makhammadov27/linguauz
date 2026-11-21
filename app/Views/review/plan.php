<?php
use App\Core\View;
use App\Models\Review;

[$sort, $dir] = [$filters['sort'], $filters['dir']];
$exportQuery = array_filter($_GET, static fn($v) => is_string($v));
unset($exportQuery['page']);
$today = date('Y-m-d');
?>
<section class="page-head">
    <div>
        <h1>Takrorlash rejasi</h1>
        <p class="muted">Rejada <strong><?= $stats['total'] ?></strong> ta so‘z · bugun <strong><?= $stats['due'] ?></strong> ta takrorlash kerak</p>
    </div>
    <div class="actions">
        <a class="btn btn-primary" href="<?= e(url('/review/session')) ?>">Takrorlashni boshlash</a>
        <a class="btn btn-secondary" href="<?= e(url('/review/export', $exportQuery)) ?>">CSV eksport</a>
    </div>
</section>

<div class="grid grid-2">
    <section class="card">
        <?php View::partial('upcoming_chart', ['upcoming' => $upcoming]); ?>
    </section>
    <section class="card">
        <h2 class="h3">Holatlar bo‘yicha</h2>
        <ul class="state-list">
            <?php foreach (['new' => 'new_count', 'learning' => 'learning', 'review' => 'review', 'mastered' => 'mastered'] as $state => $key): ?>
                <li>
                    <a href="<?= e(url('/review', ['state' => $state])) ?>"><span class="badge state-<?= $state ?>"><?= e(Review::STATES[$state]) ?></span></a>
                    <strong><?= $stats[$key] ?></strong>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $stats['progress'] ?>" aria-label="O‘zlashtirish foizi">
            <span class="progress-fill" style="width: <?= $stats['progress'] ?>%"></span>
        </div>
        <p class="muted small"><?= $stats['progress'] ?>% o‘zlashtirilgan</p>
    </section>
</div>

<form class="filters card section" method="get" action="<?= e(url('/review')) ?>" role="search" aria-label="Rejani filtrlash">
    <div class="field field-grow">
        <label for="q">Qidiruv</label>
        <input type="search" id="q" name="q" value="<?= e($filters['q']) ?>" maxlength="100" placeholder="So‘z yoki tarjima…"
               data-live-filter="#plan-table tbody tr" autocomplete="off">
    </div>
    <div class="field">
        <label for="collection_id">To‘plam</label>
        <select id="collection_id" name="collection_id" data-autosubmit>
            <option value="">Barchasi</option>
            <?php foreach ($collections as $c): ?>
                <option value="<?= (int) $c['id'] ?>"<?= $filters['collection_id'] === (int) $c['id'] ? ' selected' : '' ?>><?= e($c['title']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="state">Holat</label>
        <select id="state" name="state" data-autosubmit>
            <option value="">Barchasi</option>
            <?php foreach (Review::STATES as $k => $label): ?>
                <option value="<?= e($k) ?>"<?= $filters['state'] === $k ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="when">Muddat</label>
        <select id="when" name="when" data-autosubmit>
            <option value="">Barchasi</option>
            <option value="due"<?= $filters['when'] === 'due' ? ' selected' : '' ?>>Bugun / muddati o‘tgan</option>
            <option value="future"<?= $filters['when'] === 'future' ? ' selected' : '' ?>>Keyingi kunlar</option>
        </select>
    </div>
    <input type="hidden" name="sort" value="<?= e($sort) ?>">
    <input type="hidden" name="dir" value="<?= e($dir) ?>">
    <div class="field field-actions">
        <button type="submit" class="btn btn-primary">Filtrlash</button>
        <a class="btn btn-ghost" href="<?= e(url('/review')) ?>">Tozalash</a>
    </div>
</form>

<?php if ($result['items']): ?>
    <p class="live-filter-status muted small" aria-live="polite" hidden></p>
    <div class="table-wrap">
        <table class="table" id="plan-table">
            <caption class="sr-only">Takrorlash rejasi</caption>
            <thead>
            <tr>
                <th scope="col"<?= aria_sort('term', $sort, $dir) ?>><?= sort_link('So‘z', 'term', $sort, $dir) ?></th>
                <th scope="col">Tarjima</th>
                <th scope="col" class="hide-sm">To‘plam</th>
                <th scope="col">Holat</th>
                <th scope="col"<?= aria_sort('due', $sort, $dir) ?>><?= sort_link('Keyingi sana', 'due', $sort, $dir) ?></th>
                <th scope="col" class="hide-sm"<?= aria_sort('interval', $sort, $dir) ?>><?= sort_link('Interval', 'interval', $sort, $dir) ?></th>
                <th scope="col" class="hide-md"<?= aria_sort('ease', $sort, $dir) ?>><?= sort_link('Osonlik', 'ease', $sort, $dir) ?></th>
                <th scope="col"><span class="sr-only">Amallar</span></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($result['items'] as $r): ?>
                <tr<?= $r['due_date'] <= $today ? ' class="row-due"' : '' ?>>
                    <td><a href="<?= e(url('/words/' . $r['word_id'])) ?>"><?= e($r['term']) ?></a></td>
                    <td><?= e($r['translation']) ?></td>
                    <td class="hide-sm small"><a href="<?= e(url('/collections/' . $r['collection_id'])) ?>"><?= e($r['collection_title']) ?></a></td>
                    <td><span class="badge state-<?= e($r['state']) ?>"><?= e(Review::STATES[$r['state']]) ?></span></td>
                    <td><?= $r['due_date'] < $today ? '<strong class="text-danger">' . e(fmt_date($r['due_date'])) . '</strong>' : ($r['due_date'] === $today ? '<strong>Bugun</strong>' : e(fmt_date($r['due_date']))) ?></td>
                    <td class="hide-sm"><?= (int) $r['interval_days'] ?> kun</td>
                    <td class="hide-md"><?= e($r['ease_factor']) ?></td>
                    <td>
                        <form method="post" action="<?= e(url('/review/' . $r['id'] . '/remove')) ?>" data-confirm="«<?= e($r['term']) ?>» so‘zini rejadan olib tashlaysizmi? Uning takrorlash tarixi o‘chadi.">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn-icon" aria-label="«<?= e($r['term']) ?>» ni rejadan olib tashlash" title="Rejadan olib tashlash">✕</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php View::partial('pagination', ['page' => $result['page'], 'pages' => $result['pages']]); ?>
<?php else: ?>
    <div class="empty card">
        <?php if ($stats['total'] === 0): ?>
            <p><strong>Reja bo‘sh.</strong></p>
            <p class="muted">To‘plam sahifasida «O‘rganishni boshlash» tugmasini bosing — so‘zlar rejaga qo‘shiladi.</p>
            <a class="btn btn-primary" href="<?= e(url('/collections')) ?>">To‘plamlarga o‘tish</a>
        <?php else: ?>
            <p><strong>Filtrga mos so‘z topilmadi.</strong></p>
            <a class="btn btn-secondary" href="<?= e(url('/review')) ?>">Filtrlarni tozalash</a>
        <?php endif; ?>
    </div>
<?php endif; ?>
