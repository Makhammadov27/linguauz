# LinguaUz — xorijiy tillarni o‘rganish web-sayti

«Web dasturlashga kirish» fani mustaqil ishi. Mavzu: **Xorijiy tillarni o‘rganishga mo‘ljallangan web-sayt**
(A ilovadagi 32-mavzu asosida: *Xorijiy til lug‘ati va takrorlash tizimi*).

Texnologiyalar: HTML5, CSS3, JavaScript (kutubxonasiz), **sof PHP 8.1+** (MVC, frameworksiz), **MySQL 8 / MariaDB 10.4+** (PDO).

## Ishga tushirish yo‘riqnomasi

### 1. Muhit talablari
| Talab | Versiya |
|---|---|
| PHP | 8.1 yoki yangi (sinovda: 8.3) |
| PHP kengaytmalari | `pdo_mysql`, `mbstring`, `fileinfo`, `openssl` (TLS pochta uchun) |
| MySQL / MariaDB | MySQL 8.0+ yoki MariaDB 10.4+ (sinovda: MariaDB 10.11, XAMPP) |
| Veb-server | Apache 2.4 (`mod_rewrite`) yoki PHP ichki serveri |

### 2. Bazani o‘rnatish
```bash
mysql -u root -p -e "CREATE DATABASE linguauz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u root -p --default-character-set=utf8mb4 linguauz < database/schema.sql
mysql -u root -p --default-character-set=utf8mb4 linguauz < database/seed.sql
```
phpMyAdmin orqali: `linguauz` bazasini yarating → *Import* → avval `schema.sql`, keyin `seed.sql`.

### 3. Sozlash
```bash
cp config/config.example.php config/config.php
```
`config/config.php` da baza foydalanuvchisi, paroli va pochta (SMTP) sozlamalarini kiriting.
Ish muhitida `'debug' => false` bo‘lishi shart. Bu fayl arxivga va repozitoriyga kiritilmaydi.

### 4. Ishga tushirish
**A) PHP ichki serveri (tezkor sinov):**
```bash
php -d upload_max_filesize=20M -d post_max_size=24M -S 127.0.0.1:8080 -t public public/index.php
```
Manzil: http://127.0.0.1:8080

**B) XAMPP / Apache:** loyiha papkasini `C:\xampp\htdocs\linguauz` ga joylang.
- Eng to‘g‘ri usul — VirtualHost ning `DocumentRoot` ini `.../linguauz/public` ga yo‘naltirish.
- Yoki http://localhost/linguauz/ manzilidan oching: ildizdagi `.htaccess` so‘rovlarni `public/` ga o‘tkazadi va
  `app/`, `config/`, `storage/`, `database/` kataloglarini tashqaridan yopadi.

**Lokal tarmoqda boshqa qurilmadan ochish:** kompyuter IP manzilini aniqlang (`ipconfig`), Windows brandmauerida 80-portga
ruxsat bering va telefondan `http://192.168.x.x/linguauz/` ni oching. Ichki server uchun: `-S 0.0.0.0:8080`.

### 5. Pochta (aloqa formasi)
Lokal sinov uchun [Mailpit](https://mailpit.axllent.org/) ni ishga tushiring: SMTP `127.0.0.1:1025`, pochta qutisi
http://127.0.0.1:8025. Haqiqiy pochta uchun `config.php` da `host`, `port=587`, `encryption='tls'`, `username`, `password`ni kiriting.
SMTP ishlamasa ham xabar bazada saqlanadi va *Boshqaruv → Aloqa xabarlari* bo‘limidan qayta yuboriladi.

### 6. Sinov hisoblari
| Rol | Pochta | Parol |
|---|---|---|
| Administrator | admin@linguauz.test | Admin123! |
| Muharrir (o‘qituvchi) | editor@linguauz.test | Editor123! |
| O‘quvchi | user@linguauz.test | User123! |
| Ikkinchi o‘quvchi (egalik sinovi) | user2@linguauz.test | User123! |

### 7. Zaxira nusxa va tiklash
```bash
mysqldump -u root -p --default-character-set=utf8mb4 --single-transaction linguauz > backups/linguauz_YYYY-MM-DD.sql
mysql -u root -p -e "CREATE DATABASE linguauz_new CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u root -p --default-character-set=utf8mb4 linguauz_new < backups/linguauz_YYYY-MM-DD.sql
```
Yuklangan fayllar `storage/uploads/` da — ularni ham alohida nusxalang.

### 8. Fayl namunalari
`samples/` katalogida himoya uchun rasm (`namuna-rasm.png`) va PDF (`grammatika-qollanma.pdf`) bor.
**Video:** 20 MB gacha istalgan kichik MP4 fayl qo‘shing (masalan, telefonda yozilgan 10 soniyalik video).

## Katalog tuzilmasi
```
public/            yagona kirish nuqtasi (index.php), assets/ (CSS, JS, rasm), .htaccess, robots.txt
app/Core/          Router, Database (PDO), Session, Auth, Csrf, Policy (RBAC), Validator, Uploader, Mailer, Csv, View
app/Models/        User, Language, Collection, Word, Exercise, Review (SM-2), File, ContactMessage, LoginAttempt
app/Controllers/   Home, Auth, Dashboard, Word, Collection, Exercise, Review, File, Contact, Admin
app/Views/         maket (layouts/main.php), bo‘limlar ko‘rinishlari, xato sahifalari
config/            sozlamalar (config.php — maxfiy, arxivga kirmaydi)
storage/           yuklangan fayllar (uploads/) va xatolar qaydi (logs/) — tashqaridan yopiq
database/          schema.sql, seed.sql
docs/              hisobot, ER-diagramma, sinov bayonnomasi
samples/           himoya uchun fayl namunalari
```

## Topshirish arxivi
`Guruh_FISH_Mavzu32.zip` — `config/config.php`, `storage/uploads/*`, `storage/logs/*` ni **kiritmang**.
