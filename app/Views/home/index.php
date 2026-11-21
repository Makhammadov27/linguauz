<?php $user = current_user(); ?>
<section class="hero">
    <div class="hero-text">
        <p class="eyebrow">Ingliz · Nemis · Rus · Turk · Koreys · Fransuz</p>
        <h1>So‘zlarni bir marta emas, <span class="accent">umrbod</span> eslab qoling</h1>
        <p class="lead">LinguaUz har bir so‘zni aynan unutishingizdan oldin qayta ko‘rsatadi. Javobingizga qarab
            keyingi takrorlash sanasini o‘zi hisoblaydi — siz faqat kuniga 10 daqiqa ajratasiz.</p>
        <div class="actions">
            <?php if ($user): ?>
                <a class="btn btn-primary btn-lg" href="<?= e(url('/review/session')) ?>">Bugungi takrorlash</a>
                <a class="btn btn-secondary btn-lg" href="<?= e(url('/collections')) ?>">To‘plamlarni ko‘rish</a>
            <?php else: ?>
                <a class="btn btn-primary btn-lg" href="<?= e(url('/register')) ?>">Bepul boshlash</a>
                <a class="btn btn-secondary btn-lg" href="<?= e(url('/collections')) ?>">To‘plamlarni ko‘rish</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="hero-card" aria-hidden="true">
        <div class="demo-card">
            <span class="demo-lang">EN · A1</span>
            <p class="demo-term">friend</p>
            <p class="demo-trans">do‘st</p>
            <div class="demo-buttons">
                <span class="q q1">Qayta<br><small>1 kun</small></span>
                <span class="q q3">Qiyin<br><small>1 kun</small></span>
                <span class="q q4">Yaxshi<br><small>6 kun</small></span>
                <span class="q q5">Oson<br><small>6 kun</small></span>
            </div>
        </div>
    </div>
</section>

<section class="stats" aria-label="Platforma statistikasi">
    <div class="stat"><strong><?= (int) $stats['languages'] ?></strong><span>til</span></div>
    <div class="stat"><strong><?= (int) $stats['collections'] ?></strong><span>ochiq to‘plam</span></div>
    <div class="stat"><strong><?= (int) $stats['words'] ?></strong><span>so‘z va ibora</span></div>
    <div class="stat"><strong><?= (int) $stats['users'] ?></strong><span>foydalanuvchi</span></div>
</section>

<section class="section">
    <h2>Qanday ishlaydi?</h2>
    <ol class="steps">
        <li class="card step">
            <span class="step-num">1</span>
            <h3>To‘plam tanlang</h3>
            <p>Darajangiz (A1–C2) va maqsadingizga mos to‘plamni oching yoki o‘z shaxsiy lug‘atingizni yarating.</p>
        </li>
        <li class="card step">
            <span class="step-num">2</span>
            <h3>Har kuni takrorlang</h3>
            <p>Kartani ko‘rib, tarjimani eslang va qanchalik oson bo‘lganini belgilang: «Qayta», «Qiyin», «Yaxshi» yoki «Oson».</p>
        </li>
        <li class="card step">
            <span class="step-num">3</span>
            <h3>Tizim rejani tuzadi</h3>
            <p>SM-2 algoritmi keyingi takrorlash sanasini hisoblaydi: 1 kun, 6 kun, 15 kun… so‘z 21 kundan uzoq eslab qolinsa — o‘zlashtirilgan.</p>
        </li>
        <li class="card step">
            <span class="step-num">4</span>
            <h3>Mashq bilan tekshiring</h3>
            <p>Variantli test va yozma tarjima mashqlari. Xato qilingan so‘zlar avtomatik bugungi takrorlashga qo‘shiladi.</p>
        </li>
    </ol>
</section>

<section class="section">
    <div class="section-head">
        <h2>Yangi to‘plamlar</h2>
        <a href="<?= e(url('/collections')) ?>">Barchasi →</a>
    </div>
    <?php if ($collections): ?>
        <div class="grid grid-3">
            <?php foreach ($collections as $c): ?>
                <?php \App\Core\View::partial('collection_card', ['c' => $c]); ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="empty">Hozircha ochiq to‘plamlar yo‘q.</p>
    <?php endif; ?>
</section>

<section class="section">
    <h2>Tillar</h2>
    <ul class="lang-list">
        <?php foreach ($languages as $l): ?>
            <li>
                <a class="lang-chip" href="<?= e(url('/words', ['language_id' => $l['id']])) ?>">
                    <span class="lang-code"><?= e(strtoupper($l['code'])) ?></span>
                    <span><?= e($l['name']) ?> <small class="muted">(<?= e($l['native_name']) ?>)</small></span>
                    <span class="badge"><?= (int) $l['public_words'] ?> so‘z</span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
