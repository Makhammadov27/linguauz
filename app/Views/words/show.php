<?php
use App\Models\Review;
use App\Models\Word;
?>
<nav class="breadcrumbs" aria-label="Yo‘l">
    <a href="<?= e(url('/words')) ?>">So‘zlar</a> ›
    <a href="<?= e(url('/collections/' . $word['collection_id'])) ?>"><?= e($word['collection_title']) ?></a> ›
    <span aria-current="page"><?= e($word['term']) ?></span>
</nav>

<article class="word-detail card">
    <header class="word-head">
        <div>
            <p class="eyebrow"><?= e($word['language_name']) ?> · <?= e(Word::POS[$word['part_of_speech']] ?? '') ?> · <?= e($word['level']) ?></p>
            <h1 class="word-term" lang="<?= e($word['language_code']) ?>"><?= e($word['term']) ?></h1>
            <?php if ($word['transcription']): ?><p class="transcription"><?= e($word['transcription']) ?></p><?php endif; ?>
        </div>
        <button type="button" class="btn btn-secondary js-only" data-speak="<?= e($word['term']) ?>" data-lang="<?= e($word['speech_code']) ?>">🔊 Talaffuz</button>
    </header>
    <p class="word-translation"><?= e($word['translation']) ?></p>

    <?php if ($word['example']): ?>
        <blockquote class="example">
            <p lang="<?= e($word['language_code']) ?>"><?= e($word['example']) ?></p>
            <?php if ($word['example_translation']): ?><footer><?= e($word['example_translation']) ?></footer><?php endif; ?>
        </blockquote>
    <?php endif; ?>

    <dl class="meta-grid">
        <div><dt>To‘plam</dt><dd><a href="<?= e(url('/collections/' . $word['collection_id'])) ?>"><?= e($word['collection_title']) ?></a></dd></div>
        <div><dt>Qo‘shgan</dt><dd><?= e($word['creator_name']) ?></dd></div>
        <div><dt>Qo‘shilgan sana</dt><dd><?= e(fmt_date($word['created_at'], 'd.m.Y H:i')) ?></dd></div>
        <?php if ($schedule): ?>
            <div><dt>Mening holatim</dt><dd><span class="badge state-<?= e($schedule['state']) ?>"><?= e(Review::STATES[$schedule['state']]) ?></span></dd></div>
            <div><dt>Keyingi takrorlash</dt><dd><?= e(fmt_date($schedule['due_date'])) ?> (interval: <?= (int) $schedule['interval_days'] ?> kun)</dd></div>
        <?php endif; ?>
    </dl>

    <?php if ($canEdit): ?>
        <div class="actions">
            <a class="btn btn-secondary" href="<?= e(url('/words/' . $word['id'] . '/edit')) ?>">Tahrirlash</a>
            <form method="post" action="<?= e(url('/words/' . $word['id'] . '/delete')) ?>" class="inline-form"
                  data-confirm="«<?= e($word['term']) ?>» so‘zini o‘chirasizmi? Bu so‘z barcha foydalanuvchilarning takrorlash rejasidan ham o‘chadi.">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger">O‘chirish</button>
            </form>
        </div>
    <?php endif; ?>
</article>
