<?php use App\Core\View; ?>
<section class="page-head">
    <div>
        <h1>Salom, <?= e($user['name']) ?>!</h1>
        <p class="muted">Rolingiz: <?= e($user['role_name']) ?></p>
    </div>
    <div class="actions">
        <a class="btn btn-primary" href="<?= e(url('/review/session')) ?>">
            Takrorlashni boshlash<?php if ($stats['due'] > 0): ?> <span class="badge badge-light"><?= $stats['due'] ?></span><?php endif; ?>
        </a>
    </div>
</section>

<section class="stats stats-dash" aria-label="Takrorlash statistikasi">
    <div class="stat stat-accent"><strong><?= $stats['due'] ?></strong><span>bugun takrorlash</span></div>
    <div class="stat"><strong><?= $stats['reviewed_today'] ?></strong><span>bugun takrorlandi</span></div>
    <div class="stat"><strong><?= $stats['total'] ?></strong><span>rejadagi so‘zlar</span></div>
    <div class="stat"><strong><?= $stats['mastered'] ?></strong><span>o‘zlashtirilgan</span></div>
</section>

<div class="grid grid-2">
    <section class="card">
        <h2 class="h3">O‘zlashtirish darajasi</h2>
        <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $stats['progress'] ?>" aria-label="O‘zlashtirish foizi">
            <span class="progress-fill" style="width: <?= $stats['progress'] ?>%"></span>
        </div>
        <p class="muted small"><?= $stats['progress'] ?>% so‘z o‘zlashtirilgan (interval ≥ 21 kun)</p>
        <ul class="state-list">
            <li><span class="badge state-new">Yangi</span> <?= $stats['new_count'] ?></li>
            <li><span class="badge state-learning">O‘rganilmoqda</span> <?= $stats['learning'] ?></li>
            <li><span class="badge state-review">Takrorlanmoqda</span> <?= $stats['review'] ?></li>
            <li><span class="badge state-mastered">O‘zlashtirilgan</span> <?= $stats['mastered'] ?></li>
        </ul>
        <?php if ($stats['due'] === 0 && $nextDue): ?>
            <p class="muted">Bugungi reja bajarildi. Keyingi takrorlash: <strong><?= e(fmt_date($nextDue)) ?></strong>.</p>
        <?php endif; ?>
    </section>
    <section class="card">
        <?php View::partial('upcoming_chart', ['upcoming' => $upcoming]); ?>
        <p><a href="<?= e(url('/review')) ?>">To‘liq takrorlash rejasi →</a></p>
    </section>
</div>

<div class="grid grid-2 section">
    <section class="card">
        <div class="section-head">
            <h2 class="h3">So‘nggi mashqlar</h2>
            <a href="<?= e(url('/exercises')) ?>">Mashqlar →</a>
        </div>
        <?php if ($attempts): ?>
            <ul class="list">
                <?php foreach ($attempts as $a): ?>
                    <li>
                        <a href="<?= e(url('/attempts/' . $a['id'])) ?>"><?= e($a['exercise_title']) ?></a>
                        <span class="badge <?= $a['percent'] >= 80 ? 'state-mastered' : ($a['percent'] >= 50 ? 'state-review' : 'state-learning') ?>">
                            <?= (int) $a['score'] ?>/<?= (int) $a['total'] ?> · <?= e(rtrim(rtrim($a['percent'], '0'), '.')) ?>%
                        </span>
                        <small class="muted"><?= e(fmt_date($a['finished_at'], 'd.m.Y H:i')) ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="empty">Hali mashq bajarmagansiz.</p>
        <?php endif; ?>
    </section>
    <section class="card">
        <div class="section-head">
            <h2 class="h3">Mening to‘plamlarim</h2>
            <a href="<?= e(url('/collections/create')) ?>">+ Yangi</a>
        </div>
        <?php if ($myCollections): ?>
            <ul class="list">
                <?php foreach ($myCollections as $c): ?>
                    <li>
                        <a href="<?= e(url('/collections/' . $c['id'])) ?>"><?= e($c['title']) ?></a>
                        <span class="muted small"><?= e($c['language_name']) ?> · <?= (int) $c['words_count'] ?> so‘z</span>
                        <?php if (!(int) $c['is_public']): ?><span class="badge badge-private">Shaxsiy</span><?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="empty">Sizda hali shaxsiy to‘plam yo‘q. <a href="<?= e(url('/collections/create')) ?>">Yarating</a> va o‘z lug‘atingizni to‘plang.</p>
        <?php endif; ?>
    </section>
</div>
