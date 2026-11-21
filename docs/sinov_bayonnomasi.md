# Sinov bayonnomasi — LinguaUz

Sinov muhiti: Windows 10, PHP 8.3 (ichki server), MariaDB 10.11 (XAMPP), Chrome asosidagi brauzer.
Boshlang‘ich holat: `schema.sql` + `seed.sql` yangidan import qilingan. Sana: 21.11.2026.
Quyidagi natijalar ishlab chiquvchi tomonidan avtomatik (curl) va brauzerda bajarilgan sinovlarda **kuzatilgan** natijalardir.
Himoyadan oldin har bir sinov uchun ekran tasvirini «Dalil» ustuniga qo‘shing.

| № | Yo‘nalish | Boshlang‘ich shart / qadamlar | Kutilgan natija | Haqiqiy natija | Dalil |
|---|---|---|---|---|---|
| T-01 | CRUD: To‘plam | user@ sifatida kirish → «Yangi to‘plam» → saqlash → tahrirlash → o‘chirish | Yozuv yaratiladi, ko‘rinadi, o‘zgaradi, o‘chadi | 303 → `/collections/9`; tahrir saqlandi; o‘chirilgach so‘zlari ham o‘chdi (0 ta) | |
| T-02 | CRUD: So‘z | O‘z to‘plamiga «apple» qo‘shish, tarjimani tahrirlash, o‘chirish | Ro‘yxat va batafsil sahifa mos | 303 → `/words/66`, tahrir saqlandi | |
| T-03 | Validatsiya | To‘plam nomi «ab», mavjud bo‘lmagan til, daraja «Z9» | Xato ko‘rsatiladi, yozuv saqlanmaydi | 422, uchta maydon xatosi | |
| T-04 | Validatsiya | Bir to‘plamga «apple» ni ikkinchi marta qo‘shish | «allaqachon mavjud» xatosi | 422 | |
| T-05 | Qidiruv | `/words?language_id=1&level=A1&sort=translation&dir=desc` | Faqat inglizcha A1 so‘zlar, tarjima bo‘yicha kamayish | 15 ta yozuv, tartib to‘g‘ri; bo‘sh natijada «Hech narsa topilmadi» | |
| T-06 | Qidiruv | `sort=term;DROP TABLE words` | Noma’lum ustun e’tiborsiz qoldiriladi | 200, standart saralash; jadval butun | |
| T-07 | Kirish | Noto‘g‘ri parol; so‘ng to‘g‘ri parol | Umumiy xato; kirish → kabinet | 422 «Elektron pochta yoki parol noto‘g‘ri»; 303 → `/dashboard` | |
| T-08 | Chiqish | Chiqishdan so‘ng eski sessiya cookie bilan `/dashboard` | Kirish sahifasiga yo‘naltiriladi | 303 → `/login` | |
| T-09 | Urinish cheklovi | 15 daqiqa ichida 5 marta noto‘g‘ri parol | Keyingi urinish bloklanadi | 5-urinishdan boshlab 429 | |
| T-10 | Rollar | O‘quvchi `/admin`, `/admin/users` ni ochadi; `POST /admin/users/3` bilan o‘ziga `role=admin` yuboradi | Serverda rad etiladi | 403; rol o‘zgarmadi | |
| T-11 | Rollar | Administrator o‘zining rolini «user» ga o‘zgartiradi | Rad etiladi | 422, rol o‘zgarmadi | |
| T-12 | Rollar | Ro‘yxatdan o‘tishda `role=admin` maydoni qo‘shib yuboriladi | E’tiborga olinmaydi | Hisob «user» roli bilan yaratildi | |
| T-13 | Yozuvga egalik | user2@ boshqa o‘quvchining `/collections/7`, `/words/58`, `/attempts/1`, `/files/4` ni ochadi | Begona ma’lumot ochilmaydi | 404 (barchasi) | |
| T-14 | Yozuvga egalik | O‘quvchi muharrirning so‘zini tahrirlash/o‘chirish, uning to‘plamiga so‘z yoki fayl qo‘shish | Rad etiladi | 403 / 422 | |
| T-15 | Web xavfsizligi | CSRF tokensiz `POST /collections` | Rad etiladi | 419 | |
| T-16 | Web xavfsizligi | Nomi `<script>alert(1)</script>`, tavsifi `' OR 1=1 --` bo‘lgan to‘plam | Oddiy matn sifatida chiqadi | `&lt;script&gt;` ko‘rinishida chiqdi; SQL buzilmadi | |
| T-17 | Joylashtirish | `mysqldump` → yangi `linguauz_restore` bazasiga import | Yozuvlar soni mos, o‘zbekcha harflar saqlanadi | words 64/64, collections 8/8, review_schedules 30/30; «do‘st» to‘g‘ri tiklandi | |
| T-18 | Fayllar | PNG (6 KB), PDF (1 KB), MP4 yuklash | Saqlanadi | uchala fayl qabul qilindi, tasodifiy nom bilan `storage/uploads` da | |
| T-19 | Fayllar | `.exe` ni `.png` deb nomlash; PHP kodni `.jpg` deb nomlash; 2,1 MB PNG; `.exe` | Rad etiladi | «aniqlangan tur: application/x-dosexec», «text/x-php», «2,0 MB dan oshmasligi kerak», «Ruxsat etilmagan fayl turi» | |
| T-20 | Fayl holati | PDF ni PNG bilan almashtirish; videoni o‘chirish | Baza va disk mos | eski fayl diskdan o‘chdi; o‘chirilgandan keyin diskda 3 fayl, bazada 3 yozuv | |
| T-21 | Fayllar | Video uchun `Range: bytes=0-99` | Qisman javob | 206, 100 bayt | |
| T-22 | Pochta | SMTP o‘chirilgan holda aloqa xabari | Xabar bazada saqlanadi | holat `failed`, xato matni qayd etildi | |
| T-23 | Pochta | Administrator «qayta yuborish» (SMTP sinov qutisi yoqilgan) | Xabar qutida ko‘rinadi | holat `sent`; qutida mavzu «LinguaUz: yangi aloqa xabari #3», Reply-To — yuboruvchi | |
| T-24 | Pochta | Bir IP dan 10 daqiqada 4-xabar | Rad etiladi | 429 | |
| T-25 | Eksport | Filtrlangan (en, A1, tarjima ↓) CSV | Ekrandagi natijaga teng, UTF-8 | BOM `EF BB BF`, sarlavha + 15 qator, tartib ekrandagidek | |
| T-26 | Eksport | `=HYPERLINK("x")` so‘zi | Formula bajarilmaydi | `'=HYPERLINK(""x"")` | |
| T-27 | Eksport | Mehmon `/words/export` | Rad etiladi | 303 → `/login` | |
| T-28 | Asosiy jarayon | «water» (reps 1, EF 2.50) → «Yaxshi» | Interval 6 kun, holat review | reps 2, interval 6, sana +6 kun, version 1→2 | |
| T-29 | Asosiy jarayon | Xuddi shu javobni eski version bilan qayta yuborish | Rad etiladi | «javob allaqachon qabul qilingan — takroriy so‘rov rad etildi» | |
| T-30 | Asosiy jarayon | Sanasi kelmagan so‘zga javob | Rad etiladi | «takrorlash sanasi hali kelmagan (03.12.2026)» | |
| T-31 | Asosiy jarayon | Boshqa foydalanuvchining takrorlash yozuviga javob / o‘chirish | Rad etiladi | «yozuv topilmadi» / 404 | |
| T-32 | Asosiy jarayon | «Qayta» (EF 2.36) | Interval 1, EF 1.82 | interval 1, EF 1.82, holat learning | |
| T-33 | Asosiy jarayon | To‘plamni rejaga ikki marta qo‘shish | Takror yozuv yo‘q | 1-marta 10 ta qo‘shildi, 2-marta «allaqachon rejangizda» | |
| T-34 | Mashq | Yozma mashq: 5 to‘g‘ri (katta harflarda), 3 xato | 5/8, xatolar rejaga qaytadi | 62,5 %; 3 so‘z bugungi takrorlashga o‘tdi, reps = 0 | |
| T-35 | Mashq | Bir xil mashq tokenini qayta yuborish | Rad etiladi | «natija allaqachon qabul qilingan» | |
| T-36 | Arxivlash | Muharrir o‘rganuvchilari bor 1-to‘plamni o‘chiradi | O‘chirilmaydi, arxivlanadi | status `archived`; o‘quvchiga 404; qaytarilgach `active` | |
| T-37 | Interfeys | 360, 768/800, 1280 px kenglikda sahifalar | Gorizontal aylantirish yo‘q | 360 px da `scrollWidth = 360`; menyu yig‘iladi | |
| T-38 | Interfeys | Klaviatura: Tab bilan forma, Space — kartani ochish, 1–4 baho | Amallar sichqonchasiz bajariladi | ishladi; konsolda xato yo‘q | |
| T-39 | Interfeys | Bo‘sh «Yangi to‘plam» formasini yuborish (JS yoqilgan) | Brauzerda xato ko‘rsatiladi | «Nomi», «Til» maydonlari xatolari chiqdi | |
| T-40 | Marshrut | `/nonexistent`, `/words/999999`, `GET /words/1/delete` | 404 / 404 / 405 | 404, 404, 405 | |
