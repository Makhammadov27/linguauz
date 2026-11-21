<?php
use App\Models\Review;
use App\Models\Word;
?>
<section class="page-head">
    <div>
        <h1>Takrorlash</h1>
        <p class="muted">Bugun takrorlanadigan so‘zlar: <strong data-due-count><?= (int) $due ?></strong> · bugun takrorlandi: <?= (int) $stats['reviewed_today'] ?></p>
    </div>
    <form method="get" action="<?= e(url('/review/session')) ?>" class="inline-filter">
        <label for="collection_id" class="sr-only">To‘plam</label>
        <select id="collection_id" name="collection_id" data-autosubmit>
            <option value="">Barcha to‘plamlar</option>
            <?php foreach ($collections as $c): ?>
                <option value="<?= (int) $c['id'] ?>"<?= $collectionId === (int) $c['id'] ? ' selected' : '' ?>><?= e($c['title']) ?></option>
            <?php endforeach; ?>
        </select>
        <noscript><button type="submit" class="btn btn-secondary btn-sm">Tanlash</button></noscript>
    </form>
</section>

<?php if ($card): ?>
    <article class="flashcard card" data-flashcard
             data-reps="<?= (int) $card['repetitions'] ?>"
             data-interval="<?= (int) $card['interval_days'] ?>"
             data-ease="<?= e($card['ease_factor']) ?>">
        <p class="flashcard-meta">
            <span class="badge state-<?= e($card['state']) ?>"><?= e(Review::STATES[$card['state']]) ?></span>
            <span class="muted small"><?= e($card['collection_title']) ?> · <?= e($card['language_name']) ?> · <?= e(Word::POS[$card['part_of_speech']] ?? '') ?></span>
        </p>

        <div class="flashcard-front">
            <p class="flashcard-term"><?= e($card['term']) ?></p>
            <?php if ($card['transcription']): ?><p class="transcription"><?= e($card['transcription']) ?></p><?php endif; ?>
            <button type="button" class="btn btn-ghost btn-sm js-only" data-speak="<?= e($card['term']) ?>" data-lang="<?= e($card['speech_code']) ?>">🔊 Eshitish</button>
        </div>

        <button type="button" class="btn btn-primary btn-lg flashcard-reveal js-only" data-reveal aria-expanded="false" aria-controls="card-back">
            Tarjimani ko‘rsatish <kbd>Space</kbd>
        </button>

        <div class="flashcard-back" id="card-back">
            <p class="flashcard-translation"><?= e($card['translation']) ?></p>
            <?php if ($card['example']): ?>
                <blockquote class="example">
                    <p><?= e($card['example']) ?></p>
                    <?php if ($card['example_translation']): ?><footer><?= e($card['example_translation']) ?></footer><?php endif; ?>
                </blockquote>
            <?php endif; ?>

            <form method="post" action="<?= e(url('/review/answer')) ?>" class="answer-form" data-answer-form>
                <?= csrf_field() ?>
                <input type="hidden" name="schedule_id" value="<?= (int) $card['id'] ?>">
                <input type="hidden" name="version" value="<?= (int) $card['version'] ?>">
                <input type="hidden" name="collection_id" value="<?= (int) $collectionId ?>">
                <p class="answer-question">So‘zni qanchalik oson esladingiz?</p>
                <div class="answer-buttons">
                    <?php $keys = [1 => '1', 3 => '2', 4 => '3', 5 => '4']; ?>
                    <?php foreach (Review::QUALITIES as $q => $label): ?>
                        <button type="submit" name="quality" value="<?= $q ?>" class="answer-btn q<?= $q ?>" data-quality="<?= $q ?>" data-key="<?= $keys[$q] ?>">
                            <span class="answer-label"><?= e($label) ?></span>
                            <span class="answer-preview" data-preview></span>
                            <kbd class="js-only"><?= $keys[$q] ?></kbd>
                        </button>
                    <?php endforeach; ?>
                </div>
            </form>
        </div>
    </article>
    <p class="muted small center">Klaviatura: <kbd>Space</kbd> — tarjimani ochish, <kbd>1</kbd>–<kbd>4</kbd> — baho berish.</p>
<?php else: ?>
    <section class="card empty done">
        <p class="done-icon" aria-hidden="true">✓</p>
        <h2>Bugungi takrorlash bajarildi!</h2>
        <?php if ($stats['total'] === 0): ?>
            <p class="muted">Takrorlash rejangiz hozircha bo‘sh. To‘plamni tanlab «O‘rganishni boshlash» tugmasini bosing.</p>
            <a class="btn btn-primary" href="<?= e(url('/collections')) ?>">To‘plamlarni ko‘rish</a>
        <?php else: ?>
            <p class="muted">Keyingi takrorlash: <strong><?= e(fmt_date($nextDue)) ?></strong>. Bo‘sh vaqtingizda mashq bajarib ko‘ring.</p>
            <div class="actions center">
                <a class="btn btn-primary" href="<?= e(url('/exercises')) ?>">Mashqlar</a>
                <a class="btn btn-secondary" href="<?= e(url('/review')) ?>">Takrorlash rejasi</a>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
