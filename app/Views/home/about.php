<article class="prose">
    <h1>Biz haqimizda</h1>
    <p class="lead">LinguaUz — o‘zbek tilida so‘zlashuvchilar uchun xorijiy tillar lug‘atini tizimli o‘rganishga mo‘ljallangan
        o‘quv web-ilovasi.</p>

    <h2>Muammo</h2>
    <p>Til o‘rganuvchilar yangi so‘zlarni tez yodlaydi, lekin bir necha kundan keyin ularning katta qismini unutadi.
        Qog‘oz lug‘at yoki oddiy ro‘yxat qaysi so‘zni qachon takrorlash kerakligini ko‘rsatmaydi.</p>

    <h2>Yechim — intervalli takrorlash</h2>
    <p>Platforma har bir so‘z uchun shaxsiy takrorlash jadvalini yuritadi. Siz so‘zni qanchalik oson eslaganingizni
        baholaysiz, tizim esa SM-2 algoritmi asosida keyingi sanani hisoblaydi:</p>
    <ul>
        <li><strong>Qayta</strong> — so‘z eslanmadi: ertaga yana ko‘rsatiladi, takrorlar hisobi nolga qaytadi.</li>
        <li><strong>Qiyin / Yaxshi / Oson</strong> — interval 1 kun → 6 kun → oldingi interval × osonlik koeffitsienti.</li>
        <li>Interval 21 kundan oshsa, so‘z <strong>o‘zlashtirilgan</strong> hisoblanadi.</li>
    </ul>

    <h2>Kimlar uchun?</h2>
    <dl class="roles">
        <dt>O‘quvchi</dt>
        <dd>Ochiq to‘plamlarni o‘rganadi, shaxsiy lug‘at yaratadi, mashq bajaradi va o‘z rejasini kuzatadi.</dd>
        <dt>Muharrir (o‘qituvchi)</dt>
        <dd>Hammaga ochiq to‘plamlar, so‘zlar, mashqlar va o‘quv materiallarini (rasm, PDF, video) tayyorlaydi.</dd>
        <dt>Administrator</dt>
        <dd>Foydalanuvchilar, rollar, tillar ro‘yxati va aloqa xabarlarini boshqaradi.</dd>
    </dl>

    <p><a class="btn btn-primary" href="<?= e(url('/collections')) ?>">To‘plamlarni ko‘rish</a></p>
</article>
