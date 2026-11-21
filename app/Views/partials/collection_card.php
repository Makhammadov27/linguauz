<?php /** @var array $c */ ?>
<article class="card collection-card<?= $c['status'] === 'archived' ? ' is-archived' : '' ?>" data-filter-item>
    <a class="collection-cover" href="<?= e(url('/collections/' . $c['id'])) ?>" tabindex="-1" aria-hidden="true">
        <?php if (!empty($c['cover_id'])): ?>
            <img src="<?= e(url('/files/' . $c['cover_id'])) ?>" alt="" loading="lazy">
        <?php else: ?>
            <span class="cover-placeholder"><?= e(strtoupper($c['language_code'])) ?></span>
        <?php endif; ?>
    </a>
    <div class="card-body">
        <div class="card-tags">
            <span class="badge badge-level"><?= e($c['level']) ?></span>
            <span class="badge"><?= e($c['language_name']) ?></span>
            <?php if (!(int) $c['is_public']): ?><span class="badge badge-private">Shaxsiy</span><?php endif; ?>
            <?php if ($c['status'] === 'archived'): ?><span class="badge badge-archived">Arxiv</span><?php endif; ?>
        </div>
        <h3 class="card-title"><a href="<?= e(url('/collections/' . $c['id'])) ?>"><?= e($c['title']) ?></a></h3>
        <?php if (!empty($c['description'])): ?>
            <p class="card-text"><?= e(mb_strimwidth((string) $c['description'], 0, 120, '…')) ?></p>
        <?php endif; ?>
        <p class="card-meta muted"><?= (int) $c['words_count'] ?> ta so‘z · <?= e($c['owner_name']) ?></p>
    </div>
</article>
