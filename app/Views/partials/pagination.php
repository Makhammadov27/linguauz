<?php
/** @var int $page @var int $pages */
if ($pages <= 1) {
    return;
}
$window = [];
for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++) {
    $window[] = $i;
}
?>
<nav class="pagination" aria-label="Sahifalash">
    <?php if ($page > 1): ?>
        <a href="<?= e(page_url(['page' => $page - 1])) ?>" rel="prev">← Oldingi</a>
    <?php else: ?>
        <span class="disabled" aria-hidden="true">← Oldingi</span>
    <?php endif; ?>

    <?php if ($window[0] > 1): ?>
        <a href="<?= e(page_url(['page' => 1])) ?>">1</a>
        <?php if ($window[0] > 2): ?><span class="gap">…</span><?php endif; ?>
    <?php endif; ?>

    <?php foreach ($window as $i): ?>
        <?php if ($i === $page): ?>
            <span class="current" aria-current="page"><?= $i ?></span>
        <?php else: ?>
            <a href="<?= e(page_url(['page' => $i])) ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if (end($window) < $pages): ?>
        <?php if (end($window) < $pages - 1): ?><span class="gap">…</span><?php endif; ?>
        <a href="<?= e(page_url(['page' => $pages])) ?>"><?= $pages ?></a>
    <?php endif; ?>

    <?php if ($page < $pages): ?>
        <a href="<?= e(page_url(['page' => $page + 1])) ?>" rel="next">Keyingi →</a>
    <?php else: ?>
        <span class="disabled" aria-hidden="true">Keyingi →</span>
    <?php endif; ?>
</nav>
