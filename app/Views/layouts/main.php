<?php
use App\Core\Session;

$user = current_user();
$appName = (string) config('app.name', 'LinguaUz');
$pageTitle = isset($title) ? $title . ' — ' . $appName : $appName . ' — xorijiy tillarni o‘rganish platformasi';
$metaDescription = $description ?? 'LinguaUz — xorijiy tillardagi so‘zlarni to‘plamlar, mashqlar va intervalli takrorlash orqali o‘rganish platformasi.';
$flashes = Session::pullFlashes();
?><!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($metaDescription) ?>">
    <?php if (!empty($noindex)): ?>
    <meta name="robots" content="noindex, nofollow">
    <?php endif; ?>
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <meta name="theme-color" content="#4f46e5">
    <link rel="icon" href="<?= e(asset('img/logo.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
    <script src="<?= e(asset('js/app.js')) ?>" defer></script>
</head>
<body>
<a class="skip-link" href="#main">Asosiy mazmunga o‘tish</a>

<header class="site-header">
    <div class="container header-inner">
        <a class="logo" href="<?= e(url('/')) ?>">
            <img src="<?= e(asset('img/logo.svg')) ?>" alt="" width="34" height="34">
            <span>Lingua<strong>Uz</strong></span>
        </a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav">
            <span class="nav-toggle-bar" aria-hidden="true"></span>
            <span class="sr-only">Menyuni ochish</span>
        </button>
        <nav id="site-nav" class="nav" aria-label="Asosiy menyu">
            <ul class="nav-links">
                <li><a href="<?= e(url('/words')) ?>"<?= nav_active('/words') ?>>So‘zlar</a></li>
                <li><a href="<?= e(url('/collections')) ?>"<?= nav_active('/collections') ?>>To‘plamlar</a></li>
                <li><a href="<?= e(url('/exercises')) ?>"<?= nav_active('/exercises') ?>>Mashqlar</a></li>
                <li><a href="<?= e(url('/review')) ?>"<?= nav_active('/review') ?>>Takrorlash</a></li>
                <li><a href="<?= e(url('/files')) ?>"<?= nav_active('/files') ?>>Materiallar</a></li>
                <li><a href="<?= e(url('/contact')) ?>"<?= nav_active('/contact') ?>>Aloqa</a></li>
            </ul>
            <div class="nav-user">
                <?php if ($user): ?>
                    <a class="nav-account" href="<?= e(url('/dashboard')) ?>"<?= nav_active('/dashboard') ?>>
                        <span class="avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($user['name'], 0, 1))) ?></span>
                        <span><?= e($user['name']) ?><small class="role-label"><?= e($user['role_name']) ?></small></span>
                    </a>
                    <?php if ($user['role'] === 'admin'): ?>
                        <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin')) ?>"<?= nav_active('/admin') ?>>Boshqaruv</a>
                    <?php endif; ?>
                    <form method="post" action="<?= e(url('/logout')) ?>" class="inline-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-ghost btn-sm">Chiqish</button>
                    </form>
                <?php else: ?>
                    <a class="btn btn-ghost btn-sm" href="<?= e(url('/login')) ?>">Kirish</a>
                    <a class="btn btn-primary btn-sm" href="<?= e(url('/register')) ?>">Ro‘yxatdan o‘tish</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>

<main id="main" class="container main" tabindex="-1">
    <?php if ($flashes): ?>
        <div class="flashes" role="status" aria-live="polite">
            <?php foreach ($flashes as $f): ?>
                <div class="alert alert-<?= e($f['type']) ?>">
                    <span><?= e($f['message']) ?></span>
                    <button type="button" class="alert-close js-only" aria-label="Xabarni yopish">×</button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?= $content ?>
</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <div>
            <p class="logo logo-footer"><span>Lingua<strong>Uz</strong></span></p>
            <p class="muted">Xorijiy tillarni intervalli takrorlash usulida o‘rganish platformasi. O‘quv loyihasi.</p>
        </div>
        <nav aria-label="Pastki menyu">
            <ul class="footer-links">
                <li><a href="<?= e(url('/')) ?>">Bosh sahifa</a></li>
                <li><a href="<?= e(url('/about')) ?>">Biz haqimizda</a></li>
                <li><a href="<?= e(url('/contact')) ?>">Aloqa</a></li>
                <li><a href="<?= e(url('/files')) ?>">O‘quv materiallari</a></li>
            </ul>
        </nav>
        <p class="muted small">© <?= date('Y') ?> LinguaUz. «Web dasturlashga kirish» fani mustaqil ishi.</p>
    </div>
</footer>
</body>
</html>
