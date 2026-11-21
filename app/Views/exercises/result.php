<?php
$percent = (float) $a['percent'];
$grade = $percent >= 90 ? 'A’lo!' : ($percent >= 70 ? 'Yaxshi natija' : ($percent >= 50 ? 'Yomon emas' : 'Ko‘proq takrorlash kerak'));
$duration = max(0, strtotime($a['finished_at']) - strtotime($a['started_at']));
?>
<nav class="breadcrumbs" aria-label="Yo‘l">
    <a href="<?= e(url('/exercises')) ?>">Mashqlar</a> ›
    <a href="<?= e(url('/exercises/' . $a['exercise_id'])) ?>"><?= e($a['exercise_title']) ?></a> ›
    <span aria-current="page">Natija</span>
</nav>

<section class="card result-head">
    <div class="score-ring" style="--p: <?= (int) round($percent) ?>" role="img" aria-label="Natija <?= (int) round($percent) ?> foiz">
        <span><?= (int) round($percent) ?>%</span>
    </div>
    <div>
        <h1><?= e($grade) ?></h1>
        <p class="lead"><?= (int) $a['score'] ?> ta to‘g‘ri javob, jami <?= (int) $a['total'] ?> ta savol.</p>
        <p class="muted small"><?= e($a['user_name']) ?> · <?= e(fmt_date($a['finished_at'], 'd.m.Y H:i')) ?> · sarflangan vaqt: <?= sprintf('%d daq %02d s', intdiv($duration, 60), $duration % 60) ?></p>
        <div class="actions">
            <a class="btn btn-primary" href="<?= e(url('/exercises/' . $a['exercise_id'] . '/play')) ?>">Qayta urinish</a>
            <a class="btn btn-secondary" href="<?= e(url('/review/session')) ?>">Takrorlashga o‘tish</a>
        </div>
    </div>
</section>

<section class="section">
    <h2>Javoblar tahlili</h2>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th scope="col">Savol</th><th scope="col">To‘g‘ri javob</th><th scope="col">Sizning javobingiz</th><th scope="col">Natija</th></tr></thead>
            <tbody>
            <?php foreach ($answers as $ans): ?>
                <tr class="<?= $ans['is_correct'] ? 'row-ok' : 'row-bad' ?>">
                    <td><?= e($ans['prompt']) ?></td>
                    <td><?= e($ans['expected']) ?></td>
                    <td><?= $ans['given_answer'] !== '' ? e($ans['given_answer']) : '<span class="muted">— javob berilmagan —</span>' ?></td>
                    <td><?= $ans['is_correct'] ? '<span class="badge state-mastered">✓ To‘g‘ri</span>' : '<span class="badge state-learning">✗ Xato</span>' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
