<?php
use App\Models\Exercise;

$total = count($questions);
$promptLang = $ex['direction'] === 'to_uz' ? substr((string) $ex['speech_code'], 0, 2) : 'uz';
?>
<section class="page-head">
    <div>
        <h1><?= e($ex['title']) ?></h1>
        <p class="muted"><?= e(Exercise::TYPES[$ex['type']]) ?> · <?= e(Exercise::DIRECTIONS[$ex['direction']]) ?> · <?= $total ?> savol</p>
    </div>
</section>

<form method="post" action="<?= e(url('/exercises/' . $ex['id'] . '/submit')) ?>" class="quiz" data-quiz>
    <?= csrf_field() ?>
    <input type="hidden" name="quiz_token" value="<?= e($token) ?>">

    <div class="quiz-progress js-only" aria-live="polite">
        <div class="progress"><span class="progress-fill" data-quiz-bar style="width:0%"></span></div>
        <p class="small"><span data-quiz-count>0</span> / <?= $total ?> javob berildi · <span data-quiz-timer>00:00</span></p>
    </div>

    <ol class="questions">
        <?php foreach ($questions as $i => $q): ?>
            <li class="card question" data-question>
                <fieldset>
                    <legend>
                        <span class="q-num"><?= $i + 1 ?>.</span>
                        <span class="q-prompt" lang="<?= e($promptLang) ?>"><?= e($q['prompt']) ?></span>
                        <?php if ($ex['direction'] === 'to_uz'): ?>
                            <button type="button" class="btn-icon js-only" data-speak="<?= e($q['prompt']) ?>" data-lang="<?= e($ex['speech_code']) ?>" aria-label="Talaffuzni eshitish">🔊</button>
                        <?php endif; ?>
                    </legend>
                    <?php if ($ex['type'] === 'choice'): ?>
                        <div class="options">
                            <?php foreach ($q['options'] as $j => $opt): ?>
                                <label class="option">
                                    <input type="radio" name="answers[<?= (int) $q['word_id'] ?>]" value="<?= e($opt) ?>">
                                    <span><?= e($opt) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <label class="sr-only" for="ans-<?= (int) $q['word_id'] ?>">Javobingiz</label>
                        <input type="text" id="ans-<?= (int) $q['word_id'] ?>" name="answers[<?= (int) $q['word_id'] ?>]" maxlength="200"
                               autocomplete="off" autocapitalize="off" spellcheck="false" placeholder="Tarjimani yozing…">
                    <?php endif; ?>
                </fieldset>
            </li>
        <?php endforeach; ?>
    </ol>

    <div class="actions">
        <button type="submit" class="btn btn-primary btn-lg">Javoblarni topshirish</button>
        <a class="btn btn-ghost" href="<?= e(url('/exercises/' . $ex['id'])) ?>">Bekor qilish</a>
    </div>
</form>
