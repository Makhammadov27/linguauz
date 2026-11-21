/**
 * LinguaUz — mijoz tomonidagi interaktiv imkoniyatlar (kutubxonasiz).
 *  1) Forma qiymatlarini tekshirish (server tekshiruvini almashtirmaydi, faqat qulaylik).
 *  2) Interaktiv qidiruv / filtrlash (jadval va kartalar bo‘yicha jonli filtr).
 *  3) Mavzuga oid hisoblash: SM-2 algoritmi bo‘yicha keyingi takrorlash intervalini oldindan ko‘rsatish,
 *     mashqdagi progress va vaqt hisoblagichi.
 */
(function () {
    'use strict';

    document.documentElement.classList.add('js');

    var $ = function (sel, root) { return (root || document).querySelector(sel); };
    var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };

    /** O‘zbekcha apostrof turlarini birxillashtirib, kichik harfga o‘tkazish */
    function normalize(text) {
        return String(text || '')
            .toLowerCase()
            .replace(/[‘’ʻʼ`´]/g, "'")
            .replace(/\s+/g, ' ')
            .trim();
    }

    /* ------------------------------------------------------------------
       Menyu (mobil), xabarlarni yopish, tasdiqlash
       ------------------------------------------------------------------ */
    function initNav() {
        var btn = $('.nav-toggle');
        var nav = $('#site-nav');
        if (!btn || !nav) { return; }
        btn.addEventListener('click', function () {
            var open = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', String(!open));
            nav.classList.toggle('is-open', !open);
            btn.querySelector('.sr-only').textContent = open ? 'Menyuni ochish' : 'Menyuni yopish';
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && nav.classList.contains('is-open')) {
                btn.click();
                btn.focus();
            }
        });
    }

    function initAlerts() {
        $$('.alert-close').forEach(function (b) {
            b.addEventListener('click', function () { b.closest('.alert').remove(); });
        });
    }

    function initConfirm() {
        $$('form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (!window.confirm(form.getAttribute('data-confirm'))) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                }
            });
        });
    }

    /* ------------------------------------------------------------------
       1) Forma qiymatlarini tekshirish
       ------------------------------------------------------------------ */
    var FILE_RULES = {
        jpg: 2, jpeg: 2, png: 2, webp: 2, // MB
        pdf: 5,
        mp4: 20
    };
    var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

    function fieldLabel(input) {
        var label = input.id ? $('label[for="' + input.id + '"]') : null;
        if (!label) {
            var fs = input.closest('fieldset');
            label = fs ? $('legend', fs) : null;
        }
        return label ? label.textContent.replace('*', '').trim() : 'Maydon';
    }

    function validateInput(input) {
        var value = input.type === 'file' ? '' : String(input.value || '').trim();
        var label = fieldLabel(input);
        var len = value.length;

        if (input.type === 'radio') {
            var group = $$('input[type="radio"][name="' + input.name + '"]', input.form);
            if (input.required && !group.some(function (r) { return r.checked; })) {
                return '«' + label + '» bo‘yicha variant tanlang.';
            }
            return '';
        }
        if (input.type === 'file') {
            if (input.required && input.files.length === 0) { return 'Fayl tanlang.'; }
            if (input.files.length && input.hasAttribute('data-file-rules')) {
                var f = input.files[0];
                var ext = (f.name.split('.').pop() || '').toLowerCase();
                if (!FILE_RULES[ext]) { return 'Faqat JPG, PNG, WEBP, PDF yoki MP4 fayl yuklash mumkin.'; }
                if (f.size > FILE_RULES[ext] * 1024 * 1024) {
                    return 'Fayl hajmi ' + FILE_RULES[ext] + ' MB dan oshmasligi kerak (tanlangan: ' + (f.size / 1048576).toFixed(1) + ' MB).';
                }
            }
            return '';
        }
        if (input.required && len === 0) { return '«' + label + '» maydoni to‘ldirilishi shart.'; }
        if (len === 0) { return ''; }
        if (input.minLength > 0 && len < input.minLength) { return '«' + label + '» kamida ' + input.minLength + ' belgidan iborat bo‘lishi kerak.'; }
        if (input.maxLength > 0 && len > input.maxLength) { return '«' + label + '» ' + input.maxLength + ' belgidan oshmasligi kerak.'; }
        if (input.type === 'email' && !EMAIL_RE.test(value)) { return 'To‘g‘ri elektron pochta manzilini kiriting (masalan: ism@example.com).'; }
        if (input.type === 'number') {
            var n = Number(value);
            if (!Number.isInteger(n)) { return '«' + label + '» butun son bo‘lishi kerak.'; }
            if (input.min !== '' && n < Number(input.min)) { return '«' + label + '» ' + input.min + ' dan kichik bo‘lmasligi kerak.'; }
            if (input.max !== '' && n > Number(input.max)) { return '«' + label + '» ' + input.max + ' dan katta bo‘lmasligi kerak.'; }
        }
        if (input.getAttribute('data-rule') === 'letter-digit' && !(/\p{L}/u.test(value) && /\d/.test(value))) {
            return 'Parolda kamida bitta harf va bitta raqam bo‘lishi kerak.';
        }
        var match = input.getAttribute('data-match');
        if (match && input.form.elements[match] && value !== input.form.elements[match].value.trim()) {
            return input.getAttribute('data-match-message') || 'Qiymatlar mos kelmadi.';
        }
        return '';
    }

    function validateRequires(input) {
        var other = input.getAttribute('data-requires');
        if (other && input.value.trim() !== '' && input.form.elements[other] && input.form.elements[other].value.trim() === '') {
            return input.getAttribute('data-requires-message') || 'Bog‘liq maydonni to‘ldiring.';
        }
        return '';
    }

    function showError(input, message) {
        var field = input.closest('.field') || input.parentElement;
        var id = (input.id || input.name) + '-jserror';
        var existing = document.getElementById(id);
        if (existing) { existing.remove(); }
        // Server xatosi qayta ko‘rsatilmasligi uchun JS tekshiruvdan so‘ng uni olib tashlaymiz
        $$('.field-error:not(.js-error)', field).forEach(function (el) { el.remove(); });
        if (message) {
            var p = document.createElement('p');
            p.className = 'field-error js-error';
            p.id = id;
            p.textContent = message;
            field.appendChild(p);
            field.classList.add('has-error');
            input.setAttribute('aria-invalid', 'true');
            input.setAttribute('aria-describedby', id);
        } else {
            field.classList.remove('has-error');
            input.removeAttribute('aria-invalid');
            if (input.getAttribute('aria-describedby') === id) { input.removeAttribute('aria-describedby'); }
        }
    }

    function initValidation() {
        $$('form[data-validate]').forEach(function (form) {
            var touched = false;
            var inputs = function () {
                return $$('input:not([type="hidden"]):not([type="checkbox"]):not([tabindex="-1"]), select, textarea', form);
            };
            var check = function (input) {
                var msg = validateInput(input) || validateRequires(input);
                if (input.type === 'radio') {
                    var first = $('input[type="radio"][name="' + input.name + '"]', form);
                    showError(first, msg);
                } else {
                    showError(input, msg);
                }
                return msg === '';
            };
            form.addEventListener('submit', function (e) {
                touched = true;
                var firstInvalid = null;
                var seenRadio = {};
                inputs().forEach(function (input) {
                    if (input.type === 'radio') {
                        if (seenRadio[input.name]) { return; }
                        seenRadio[input.name] = true;
                    }
                    if (!check(input) && !firstInvalid) { firstInvalid = input; }
                });
                if (firstInvalid) {
                    e.preventDefault();
                    firstInvalid.focus();
                }
            });
            form.addEventListener('input', function (e) {
                if (touched && e.target.matches('input, select, textarea')) { check(e.target); }
            });
            form.addEventListener('change', function (e) {
                if (e.target.type === 'file' || touched) { check(e.target); }
            });
        });
    }

    function initCounters() {
        $$('textarea[data-counter][maxlength]').forEach(function (ta) {
            var max = Number(ta.getAttribute('maxlength'));
            var out = document.createElement('p');
            out.className = 'counter';
            out.setAttribute('aria-live', 'polite');
            ta.insertAdjacentElement('afterend', out);
            var update = function () {
                out.textContent = ta.value.length + ' / ' + max;
                out.classList.toggle('is-over', ta.value.length > max * 0.9);
            };
            ta.addEventListener('input', update);
            update();
        });
    }

    /* ------------------------------------------------------------------
       2) Interaktiv qidiruv va filtrlash
       ------------------------------------------------------------------ */
    function initAutoSubmit() {
        $$('[data-autosubmit]').forEach(function (el) {
            el.addEventListener('change', function () {
                var form = el.form;
                if (!form) { return; }
                var page = form.querySelector('input[name="page"]');
                if (page) { page.value = '1'; }
                if (form.requestSubmit) { form.requestSubmit(); } else { form.submit(); }
            });
        });
    }

    function initLiveFilter() {
        $$('[data-live-filter]').forEach(function (input) {
            var items = $$(input.getAttribute('data-live-filter'));
            if (!items.length) { return; }
            var scope = input.closest('form') ? document : input.closest('section') || document;
            var status = $('.live-filter-status', scope === document ? document : scope);
            var timer = null;
            var apply = function () {
                var q = normalize(input.value);
                var shown = 0;
                items.forEach(function (item) {
                    var hit = q === '' || normalize(item.textContent).indexOf(q) !== -1;
                    item.classList.toggle('is-filtered-out', !hit);
                    if (hit) { shown++; }
                });
                if (status) {
                    status.hidden = q === '';
                    status.textContent = q === '' ? '' : 'Joriy sahifada mos keladi: ' + shown + ' ta. To‘liq qidiruv uchun Enter tugmasini bosing.';
                }
            };
            input.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(apply, 120);
            });
            // Sahifa yangilangandan keyin server natijasi allaqachon filtrlangan — qayta yashirmaymiz
        });
    }

    /* ------------------------------------------------------------------
       Talaffuz (Web Speech API)
       ------------------------------------------------------------------ */
    function initSpeech() {
        var buttons = $$('[data-speak]');
        if (!('speechSynthesis' in window)) {
            buttons.forEach(function (b) { b.remove(); });
            return;
        }
        buttons.forEach(function (b) {
            b.addEventListener('click', function () {
                var u = new SpeechSynthesisUtterance(b.getAttribute('data-speak'));
                u.lang = b.getAttribute('data-lang') || 'en-US';
                u.rate = 0.9;
                window.speechSynthesis.cancel();
                window.speechSynthesis.speak(u);
            });
        });
    }

    /* ------------------------------------------------------------------
       3) Mavzuga oid hisoblash: SM-2 bo‘yicha keyingi interval
       (serverdagi App\Models\Review::compute() bilan bir xil formula)
       ------------------------------------------------------------------ */
    function sm2(reps, interval, ease, quality) {
        if (quality < 3) {
            reps = 0;
            interval = 1;
        } else {
            reps += 1;
            if (reps === 1) { interval = 1; } else if (reps === 2) { interval = 6; } else { interval = Math.max(1, Math.round(interval * ease)); }
        }
        ease = ease + (0.1 - (5 - quality) * (0.08 + (5 - quality) * 0.02));
        ease = Math.round(Math.max(1.3, Math.min(5, ease)) * 100) / 100;
        return { reps: reps, interval: Math.min(interval, 365), ease: ease };
    }

    function formatInterval(days) {
        if (days >= 60) { return '≈' + Math.round(days / 30) + ' oy'; }
        return days + ' kun';
    }

    function initFlashcard() {
        var card = $('[data-flashcard]');
        if (!card) { return; }
        var reps = Number(card.getAttribute('data-reps'));
        var interval = Number(card.getAttribute('data-interval'));
        var ease = Number(card.getAttribute('data-ease'));
        var reveal = $('[data-reveal]', card);
        var form = $('[data-answer-form]', card);

        $$('[data-quality]', card).forEach(function (btn) {
            var res = sm2(reps, interval, ease, Number(btn.getAttribute('data-quality')));
            var target = new Date();
            target.setDate(target.getDate() + res.interval);
            var preview = $('[data-preview]', btn);
            preview.textContent = formatInterval(res.interval);
            btn.title = 'Keyingi takrorlash: ' + target.toLocaleDateString('uz-UZ') + ' · osonlik koeffitsienti: ' + res.ease.toFixed(2);
        });

        var show = function () {
            card.classList.add('is-revealed');
            if (reveal) { reveal.setAttribute('aria-expanded', 'true'); }
            var first = $('[data-quality="4"]', card);
            if (first) { first.focus(); }
        };
        if (reveal) { reveal.addEventListener('click', show); }

        // Takroriy yuborishning oldini olish (server ham versiya orqali tekshiradi)
        var sent = false;
        form.addEventListener('submit', function (e) {
            if (sent) { e.preventDefault(); return; }
            sent = true;
            // Tugma qiymati forma ma’lumotiga qo‘shilgandan so‘ng tugmalar o‘chiriladi
            setTimeout(function () {
                $$('[data-quality]', card).forEach(function (b) { b.disabled = true; });
            }, 0);
        });

        document.addEventListener('keydown', function (e) {
            if (e.target.matches('input, textarea, select') || e.ctrlKey || e.metaKey || e.altKey) { return; }
            if ((e.key === ' ' || e.key === 'Enter') && !card.classList.contains('is-revealed')) {
                e.preventDefault();
                show();
                return;
            }
            if (card.classList.contains('is-revealed')) {
                var btn = $('[data-key="' + e.key + '"]', card);
                if (btn && !btn.disabled) {
                    e.preventDefault();
                    btn.click();
                }
            }
        });
    }

    /* ------------------------------------------------------------------
       Mashq: javoblar soni, progress va vaqt
       ------------------------------------------------------------------ */
    function initQuiz() {
        var form = $('[data-quiz]');
        if (!form) { return; }
        var questions = $$('[data-question]', form);
        var bar = $('[data-quiz-bar]', form);
        var count = $('[data-quiz-count]', form);
        var timerEl = $('[data-quiz-timer]', form);
        var started = Date.now();
        var submitting = false;

        var answered = function (q) {
            var radio = $('input[type="radio"]:checked', q);
            var text = $('input[type="text"]', q);
            return Boolean(radio) || Boolean(text && text.value.trim() !== '');
        };
        var update = function () {
            var n = 0;
            questions.forEach(function (q) {
                var ok = answered(q);
                q.classList.toggle('is-answered', ok);
                if (ok) { n++; }
            });
            count.textContent = String(n);
            bar.style.width = Math.round(n * 100 / questions.length) + '%';
            return n;
        };
        form.addEventListener('input', update);
        form.addEventListener('change', update);
        update();

        setInterval(function () {
            var s = Math.floor((Date.now() - started) / 1000);
            timerEl.textContent = String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');
        }, 1000);

        form.addEventListener('submit', function (e) {
            if (submitting) { e.preventDefault(); return; }
            var left = questions.length - update();
            if (left > 0 && !window.confirm(left + ' ta savolga javob berilmagan. Baribir topshirasizmi?')) {
                e.preventDefault();
                return;
            }
            submitting = true;
            $$('button[type="submit"]', form).forEach(function (b) { b.disabled = true; b.textContent = 'Tekshirilmoqda…'; });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initNav();
        initAlerts();
        initConfirm();
        initValidation();
        initCounters();
        initAutoSubmit();
        initLiveFilter();
        initSpeech();
        initFlashcard();
        initQuiz();
    });
})();
