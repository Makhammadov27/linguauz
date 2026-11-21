<?php
use App\Core\View;
use App\Models\Collection;
use App\Models\Word;

[$sort, $dir] = [$filters['sort'], $filters['dir']];
$exportQuery = array_filter($_GET, static fn($v) => is_string($v));
unset($exportQuery['page']);
?>
<section class="page-head">
    <div>
        <h1>So‘zlar lug‘ati</h1>
        <p class="muted">Topildi: <strong><?= (int) $result['total'] ?></strong> ta so‘z</p>
    </div>
    <div class="actions">
        <?php if ($user): ?>
            <a class="btn btn-primary" href="<?= e(url('/words/create', $filters['collection_id'] ? ['collection_id' => $filters['collection_id']] : [])) ?>">+ Yangi so‘z</a>
            <a class="btn btn-secondary" href="<?= e(url('/words/export', $exportQuery)) ?>">CSV eksport</a>
        <?php endif; ?>
    </div>
</section>

<form class="filters card" method="get" action="<?= e(url('/words')) ?>" role="search" aria-label="So‘zlarni qidirish va filtrlash">
    <div class="field field-grow">
        <label for="q">Qidiruv</label>
        <input type="search" id="q" name="q" value="<?= e($filters['q']) ?>" maxlength="100"
               placeholder="So‘z, tarjima yoki misol…" data-live-filter="#words-table tbody tr" autocomplete="off">
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
        <label for="collection_id">To‘plam</label>
        <select id="collection_id" name="collection_id" data-autosubmit>
            <option value="">Barchasi</option>
            <?php foreach ($collections as $c): ?>
                <option value="<?= (int) $c['id'] ?>"<?= $filters['collection_id'] === (int) $c['id'] ? ' selected' : '' ?>><?= e($c['title']) ?></option>
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
        <label for="pos">So‘z turkumi</label>
        <select id="pos" name="pos" data-autosubmit>
            <option value="">Barchasi</option>
            <?php foreach (Word::POS as $k => $label): ?>
                <option value="<?= e($k) ?>"<?= $filters['pos'] === $k ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php if ($user): ?>
        <div class="field field-check">
            <input type="checkbox" id="mine" name="mine" value="1"<?= $filters['mine'] ? ' checked' : '' ?> data-autosubmit>
            <label for="mine">Faqat mening so‘zlarim</label>
        </div>
    <?php endif; ?>
    <input type="hidden" name="sort" value="<?= e($sort) ?>">
    <input type="hidden" name="dir" value="<?= e($dir) ?>">
    <div class="field field-actions">
        <button type="submit" class="btn btn-primary">Qidirish</button>
        <a class="btn btn-ghost" href="<?= e(url('/words')) ?>">Tozalash</a>
    </div>
</form>

<?php if ($result['items']): ?>
    <p class="live-filter-status muted small" aria-live="polite" hidden></p>
    <div class="table-wrap">
        <table class="table" id="words-table">
            <caption class="sr-only">So‘zlar ro‘yxati, <?= (int) $result['page'] ?>-sahifa</caption>
            <thead>
            <tr>
                <th scope="col"<?= aria_sort('term', $sort, $dir) ?>><?= sort_link('So‘z', 'term', $sort, $dir) ?></th>
                <th scope="col"<?= aria_sort('translation', $sort, $dir) ?>><?= sort_link('Tarjima', 'translation', $sort, $dir) ?></th>
                <th scope="col" class="hide-sm">Turkum</th>
                <th scope="col" class="hide-sm"<?= aria_sort('collection', $sort, $dir) ?>><?= sort_link('To‘plam', 'collection', $sort, $dir) ?></th>
                <th scope="col"<?= aria_sort('language', $sort, $dir) ?>><?= sort_link('Til', 'language', $sort, $dir) ?></th>
                <th scope="col" class="hide-sm"<?= aria_sort('level', $sort, $dir) ?>><?= sort_link('Daraja', 'level', $sort, $dir) ?></th>
                <th scope="col" class="hide-md"<?= aria_sort('created', $sort, $dir) ?>><?= sort_link('Qo‘shilgan', 'created', $sort, $dir) ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($result['items'] as $w): ?>
                <tr>
                    <td>
                        <a class="word-link" href="<?= e(url('/words/' . $w['id'])) ?>"><?= e($w['term']) ?></a>
                        <button type="button" class="btn-icon js-only" data-speak="<?= e($w['term']) ?>" data-lang="<?= e($w['speech_code']) ?>" aria-label="«<?= e($w['term']) ?>» talaffuzini eshitish">🔊</button>
                        <?php if ($w['transcription']): ?><small class="muted d-block"><?= e($w['transcription']) ?></small><?php endif; ?>
                    </td>
                    <td><?= e($w['translation']) ?></td>
                    <td class="hide-sm"><?= e(Word::POS[$w['part_of_speech']] ?? '') ?></td>
                    <td class="hide-sm"><a href="<?= e(url('/collections/' . $w['collection_id'])) ?>"><?= e($w['collection_title']) ?></a></td>
                    <td><span class="badge"><?= e(strtoupper($w['language_code'])) ?></span></td>
                    <td class="hide-sm"><span class="badge badge-level"><?= e($w['level']) ?></span></td>
                    <td class="hide-md muted small"><?= e(fmt_date($w['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php View::partial('pagination', ['page' => $result['page'], 'pages' => $result['pages']]); ?>
<?php else: ?>
    <div class="empty card">
        <p><strong>Hech narsa topilmadi.</strong></p>
        <p class="muted">Qidiruv so‘zini o‘zgartiring yoki filtrlarni tozalang.</p>
        <a class="btn btn-secondary" href="<?= e(url('/words')) ?>">Filtrlarni tozalash</a>
    </div>
<?php endif; ?>
