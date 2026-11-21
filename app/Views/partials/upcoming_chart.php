<?php
/** @var array<string,int> $upcoming */
$max = max(1, ...array_values($upcoming));
$weekdays = ['Ya', 'Du', 'Se', 'Ch', 'Pa', 'Ju', 'Sh'];
?>
<figure class="chart" aria-labelledby="chart-caption">
    <figcaption id="chart-caption" class="chart-caption">Keyingi 7 kunda takrorlanadigan so‘zlar</figcaption>
    <ol class="chart-bars">
        <?php $i = 0; foreach ($upcoming as $date => $n): ?>
            <?php $d = new DateTimeImmutable($date); ?>
            <li class="chart-bar<?= $i === 0 ? ' is-today' : '' ?>">
                <span class="chart-value"><?= $n ?></span>
                <span class="chart-fill" style="height: <?= (int) round($n * 100 / $max) ?>%"></span>
                <span class="chart-label"><?= $i === 0 ? 'Bugun' : e($weekdays[(int) $d->format('w')] . ' ' . $d->format('d')) ?></span>
                <span class="sr-only"><?= e($d->format('d.m.Y')) ?>: <?= $n ?> ta so‘z</span>
            </li>
        <?php $i++; endforeach; ?>
    </ol>
</figure>
