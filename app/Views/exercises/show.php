<?php use App\Models\Exercise; ?>
<nav class="breadcrumbs" aria-label="Yo‘l">
    <a href="<?= e(url('/exercises')) ?>">Mashqlar</a> › <span aria-current="page"><?= e($ex['title']) ?></span>
</nav>

<section class="card exercise-detail">
    <div class="card-tags">
        <span class="badge badge-level"><?= e($ex['level']) ?></span>
        <span class="badge"><?= e($ex['language_name']) ?></span>
        <span class="badge badge-type"><?= e(Exercise::TYPES[$ex['type']]) ?></span>
    </div>
    <h1><?= e($ex['title']) ?></h1>
    <dl class="meta-grid">
        <div><dt>To‘plam</dt><dd><a href="<?= e(url('/collections/' . $ex['collection_id'])) ?>"><?= e($ex['collection_title']) ?></a></dd></div>
        <div><dt>Yo‘nalish</dt><dd><?= e(Exercise::DIRECTIONS[$ex['direction']]) ?></dd></div>
        <div><dt>Savollar</dt><dd><?= (int) min($ex['question_count'], $ex['words_count']) ?> ta (to‘plamda <?= (int) $ex['words_count'] ?> so‘z)</dd></div>
        <div><dt>Muallif</dt><dd><?= e($ex['creator_name']) ?></dd></div>
    </dl>
    <p class="muted small">Xato javob berilgan so‘zlar, agar ular takrorlash rejangizda bo‘lsa, avtomatik ravishda bugungi takrorlashga qo‘shiladi.</p>
    <div class="actions">
        <a class="btn btn-primary btn-lg" href="<?= e(url('/exercises/' . $ex['id'] . '/play')) ?>">Mashqni boshlash</a>
        <?php if ($canEdit): ?>
            <a class="btn btn-secondary" href="<?= e(url('/exercises/' . $ex['id'] . '/edit')) ?>">Tahrirlash</a>
            <form method="post" action="<?= e(url('/exercises/' . $ex['id'] . '/delete')) ?>" class="inline-form"
                  data-confirm="«<?= e($ex['title']) ?>» mashqini va uning barcha natijalarini o‘chirasizmi?">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger">O‘chirish</button>
            </form>
        <?php endif; ?>
    </div>
</section>

<?php if ($user): ?>
<section class="section">
    <h2>Mening urinishlarim</h2>
    <?php if ($attempts): ?>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th scope="col">Sana</th><th scope="col">Natija</th><th scope="col">Foiz</th><th scope="col"><span class="sr-only">Batafsil</span></th></tr></thead>
                <tbody>
                <?php foreach ($attempts as $a): ?>
                    <tr>
                        <td><?= e(fmt_date($a['finished_at'], 'd.m.Y H:i')) ?></td>
                        <td><?= (int) $a['score'] ?> / <?= (int) $a['total'] ?></td>
                        <td><?= e(rtrim(rtrim((string) $a['percent'], '0'), '.')) ?>%</td>
                        <td><a href="<?= e(url('/attempts/' . $a['id'])) ?>">Batafsil</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="empty">Bu mashqni hali bajarmagansiz.</p>
    <?php endif; ?>
</section>
<?php endif; ?>
