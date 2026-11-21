<?php
use App\Models\Exercise;
use App\Models\File;
use App\Models\Word;

$images = array_values(array_filter($files, static fn($f) => $f['category'] === 'image'));
$pdfs = array_values(array_filter($files, static fn($f) => $f['category'] === 'pdf'));
$videos = array_values(array_filter($files, static fn($f) => $f['category'] === 'video'));
$archived = $c['status'] === 'archived';
?>
<nav class="breadcrumbs" aria-label="Yo‘l">
    <a href="<?= e(url('/collections')) ?>">To‘plamlar</a> › <span aria-current="page"><?= e($c['title']) ?></span>
</nav>

<section class="collection-head card">
    <div class="collection-head-main">
        <div class="card-tags">
            <span class="badge badge-level"><?= e($c['level']) ?></span>
            <span class="badge"><?= e($c['language_name']) ?> · <?= e($c['native_name']) ?></span>
            <?php if (!(int) $c['is_public']): ?><span class="badge badge-private">Shaxsiy</span><?php endif; ?>
            <?php if ($archived): ?><span class="badge badge-archived">Arxivlangan</span><?php endif; ?>
        </div>
        <h1><?= e($c['title']) ?></h1>
        <?php if ($c['description']): ?><p class="lead"><?= nl2br(e($c['description'])) ?></p><?php endif; ?>
        <p class="muted small">Muallif: <?= e($c['owner_name']) ?> · <?= (int) $c['words_count'] ?> ta so‘z · yaratilgan <?= e(fmt_date($c['created_at'])) ?>
            <?php if ($canEdit): ?> · o‘rganuvchilar: <?= (int) $learners ?><?php endif; ?></p>
    </div>
    <div class="collection-head-side">
        <?php if ($user && !$archived): ?>
            <?php if ($inPlan > 0 && $inPlan >= (int) $c['words_count']): ?>
                <p class="muted small">Barcha so‘zlar rejangizda</p>
                <a class="btn btn-primary btn-block" href="<?= e(url('/review/session', ['collection_id' => $c['id']])) ?>">Takrorlash</a>
            <?php else: ?>
                <form method="post" action="<?= e(url('/collections/' . $c['id'] . '/start')) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary btn-block"<?= (int) $c['words_count'] === 0 ? ' disabled' : '' ?>>
                        <?= $inPlan > 0 ? 'Yangi so‘zlarni rejaga qo‘shish' : 'O‘rganishni boshlash' ?>
                    </button>
                </form>
                <?php if ($inPlan > 0): ?><p class="muted small"><?= (int) $inPlan ?> ta so‘z rejangizda</p><?php endif; ?>
            <?php endif; ?>
        <?php elseif (!$user): ?>
            <a class="btn btn-primary btn-block" href="<?= e(url('/login')) ?>">O‘rganish uchun kiring</a>
        <?php endif; ?>

        <?php if ($canEdit): ?>
            <div class="actions actions-stack">
                <?php if (!$archived): ?>
                    <a class="btn btn-secondary btn-sm" href="<?= e(url('/words/create', ['collection_id' => $c['id']])) ?>">+ So‘z</a>
                    <a class="btn btn-secondary btn-sm" href="<?= e(url('/exercises/create', ['collection_id' => $c['id']])) ?>">+ Mashq</a>
                    <a class="btn btn-ghost btn-sm" href="<?= e(url('/collections/' . $c['id'] . '/edit')) ?>">Tahrirlash</a>
                    <form method="post" action="<?= e(url('/collections/' . $c['id'] . '/delete')) ?>" class="inline-form"
                          data-confirm="«<?= e($c['title']) ?>» to‘plamini o‘chirasizmi? Undagi so‘zlar, mashqlar va fayllar ham o‘chadi. Agar uni boshqa foydalanuvchilar o‘rganayotgan bo‘lsa, to‘plam arxivlanadi.">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger btn-sm">O‘chirish</button>
                    </form>
                <?php else: ?>
                    <form method="post" action="<?= e(url('/collections/' . $c['id'] . '/restore')) ?>" class="inline-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-secondary btn-sm">Arxivdan qaytarish</button>
                    </form>
                    <?php if ($user['role'] === 'admin'): ?>
                        <form method="post" action="<?= e(url('/collections/' . $c['id'] . '/delete')) ?>" class="inline-form"
                              data-confirm="To‘plam butunlay o‘chiriladi va o‘rganuvchilarning tarixi ham yo‘qoladi. Davom etasizmi?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="force" value="1">
                            <button type="submit" class="btn btn-danger btn-sm">Butunlay o‘chirish</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if ($images || $videos || $pdfs || $canEdit): ?>
<section class="section">
    <h2>O‘quv materiallari</h2>
    <?php if ($images): ?>
        <div class="gallery">
            <?php foreach ($images as $f): ?>
                <figure>
                    <img src="<?= e(url('/files/' . $f['id'])) ?>" alt="<?= e($f['description'] ?: $f['original_name']) ?>" loading="lazy">
                    <?php if ($f['description']): ?><figcaption><?= e($f['description']) ?></figcaption><?php endif; ?>
                </figure>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php foreach ($videos as $f): ?>
        <figure class="video">
            <video controls preload="metadata" src="<?= e(url('/files/' . $f['id'])) ?>">
                Brauzeringiz videoni qo‘llab-quvvatlamaydi. <a href="<?= e(url('/files/' . $f['id'], ['download' => 1])) ?>">Yuklab olish</a>
            </video>
            <figcaption><?= e($f['description'] ?: $f['original_name']) ?></figcaption>
        </figure>
    <?php endforeach; ?>
    <?php if ($pdfs): ?>
        <ul class="file-list">
            <?php foreach ($pdfs as $f): ?>
                <li>
                    <span class="file-icon" aria-hidden="true">PDF</span>
                    <a href="<?= e(url('/files/' . $f['id'])) ?>" target="_blank" rel="noopener"><?= e($f['original_name']) ?></a>
                    <span class="muted small"><?= e(human_size((int) $f['size_bytes'])) ?><?= $f['description'] ? ' · ' . e($f['description']) : '' ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($canEdit && !$archived): ?>
        <details class="card upload-box"<?= !$files ? ' open' : '' ?>>
            <summary>Fayllarni boshqarish</summary>
            <form method="post" action="<?= e(url('/collections/' . $c['id'] . '/files')) ?>" enctype="multipart/form-data" class="form" data-validate novalidate>
                <?= csrf_field() ?>
                <div class="grid grid-2 grid-tight">
                    <div class="field">
                        <label for="file">Yangi fayl</label>
                        <input type="file" id="file" name="file" required accept=".jpg,.jpeg,.png,.webp,.pdf,.mp4" data-file-rules aria-describedby="file-hint">
                        <p class="hint" id="file-hint">
                            <?php foreach ($uploadRules as $rule): ?>
                                <?= e($rule['label']) ?>: <?= e(strtoupper(implode(', ', $rule['ext']))) ?> — <?= e(human_size($rule['max'])) ?> gacha.
                            <?php endforeach; ?>
                        </p>
                    </div>
                    <div class="field">
                        <label for="description">Tavsif</label>
                        <input type="text" id="description" name="description" maxlength="255" placeholder="Masalan: mavzu bo‘yicha qo‘llanma">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Yuklash</button>
            </form>

            <?php if ($files): ?>
                <div class="table-wrap">
                    <table class="table table-compact">
                        <caption class="sr-only">Biriktirilgan fayllar</caption>
                        <thead><tr><th scope="col">Fayl</th><th scope="col">Turi / hajmi</th><th scope="col">Almashtirish yoki tavsifni o‘zgartirish</th><th scope="col"><span class="sr-only">O‘chirish</span></th></tr></thead>
                        <tbody>
                        <?php foreach ($files as $f): ?>
                            <tr>
                                <td><a href="<?= e(url('/files/' . $f['id'])) ?>" target="_blank" rel="noopener"><?= e($f['original_name']) ?></a></td>
                                <td class="small"><?= e(File::CATEGORIES[$f['category']]) ?><br><span class="muted"><?= e(human_size((int) $f['size_bytes'])) ?></span></td>
                                <td>
                                    <form method="post" action="<?= e(url('/files/' . $f['id'] . '/replace')) ?>" enctype="multipart/form-data" class="replace-form">
                                        <?= csrf_field() ?>
                                        <label class="sr-only" for="desc-<?= (int) $f['id'] ?>">Tavsif</label>
                                        <input type="text" id="desc-<?= (int) $f['id'] ?>" name="description" maxlength="255" value="<?= e($f['description'] ?? '') ?>" placeholder="Tavsif">
                                        <label class="sr-only" for="rep-<?= (int) $f['id'] ?>">Yangi fayl (ixtiyoriy)</label>
                                        <input type="file" id="rep-<?= (int) $f['id'] ?>" name="file" accept=".jpg,.jpeg,.png,.webp,.pdf,.mp4" data-file-rules>
                                        <button type="submit" class="btn btn-secondary btn-sm">Saqlash</button>
                                    </form>
                                </td>
                                <td>
                                    <form method="post" action="<?= e(url('/files/' . $f['id'] . '/delete')) ?>" data-confirm="«<?= e($f['original_name']) ?>» faylini olib tashlaysizmi?">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-danger btn-sm">O‘chirish</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </details>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php if ($exercises): ?>
<section class="section">
    <h2>Mashqlar</h2>
    <ul class="grid grid-3 plain">
        <?php foreach ($exercises as $ex): ?>
            <li class="card exercise-mini">
                <h3 class="card-title"><a href="<?= e(url('/exercises/' . $ex['id'])) ?>"><?= e($ex['title']) ?></a></h3>
                <p class="muted small"><?= e(Exercise::TYPES[$ex['type']]) ?> · <?= (int) $ex['question_count'] ?> savol · <?= e(Exercise::DIRECTIONS[$ex['direction']]) ?></p>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php endif; ?>

<section class="section">
    <div class="section-head">
        <h2>So‘zlar (<?= count($words) ?>)</h2>
        <?php if ($words): ?>
            <div class="field field-inline">
                <label for="word-filter" class="sr-only">To‘plam ichida qidirish</label>
                <input type="search" id="word-filter" placeholder="Shu to‘plamda qidirish…" data-live-filter="#collection-words tbody tr" autocomplete="off">
            </div>
        <?php endif; ?>
    </div>
    <?php if ($words): ?>
        <p class="live-filter-status muted small" aria-live="polite" hidden></p>
        <div class="table-wrap">
            <table class="table" id="collection-words">
                <caption class="sr-only">To‘plamdagi so‘zlar</caption>
                <thead><tr><th scope="col">So‘z</th><th scope="col">Tarjima</th><th scope="col" class="hide-sm">Turkum</th><th scope="col" class="hide-md">Misol</th></tr></thead>
                <tbody>
                <?php foreach ($words as $w): ?>
                    <tr>
                        <td>
                            <a class="word-link" href="<?= e(url('/words/' . $w['id'])) ?>" lang="<?= e($c['language_code']) ?>"><?= e($w['term']) ?></a>
                            <button type="button" class="btn-icon js-only" data-speak="<?= e($w['term']) ?>" data-lang="<?= e($c['speech_code']) ?>" aria-label="«<?= e($w['term']) ?>» talaffuzi">🔊</button>
                            <?php if ($w['transcription']): ?><small class="muted d-block"><?= e($w['transcription']) ?></small><?php endif; ?>
                        </td>
                        <td><?= e($w['translation']) ?></td>
                        <td class="hide-sm"><?= e(Word::POS[$w['part_of_speech']] ?? '') ?></td>
                        <td class="hide-md small muted" lang="<?= e($c['language_code']) ?>"><?= e((string) $w['example']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="empty card">Bu to‘plamda hali so‘z yo‘q.<?php if ($canEdit && !$archived): ?> <a href="<?= e(url('/words/create', ['collection_id' => $c['id']])) ?>">Birinchi so‘zni qo‘shing</a>.<?php endif; ?></p>
    <?php endif; ?>
</section>
