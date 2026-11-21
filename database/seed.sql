-- =====================================================================
-- LinguaUz — sinov (namunaviy) ma’lumotlari
-- schema.sql dan keyin import qilinadi:  mysql -u root -p linguauz < database/seed.sql
--
-- Sinov hisoblari (parollar faqat o‘quv maqsadida):
--   admin@linguauz.test   / Admin123!   — administrator
--   editor@linguauz.test  / Editor123!  — muharrir (o‘qituvchi)
--   user@linguauz.test    / User123!    — o‘quvchi
--   user2@linguauz.test   / User123!    — ikkinchi o‘quvchi (egalik sinovlari uchun)
-- =====================================================================
SET NAMES utf8mb4;

INSERT INTO roles (id, slug, name) VALUES
(1, 'admin',  'Administrator'),
(2, 'editor', 'Muharrir (o‘qituvchi)'),
(3, 'user',   'O‘quvchi');

INSERT INTO users (id, role_id, name, email, password_hash) VALUES
(1, 1, 'Tizim administratori', 'admin@linguauz.test',  '$2y$10$nf4v6hlnHOP.Ivs5FD64OexfHAJ3YLIw1aiM6w0v7ld63tpWxXqka'),
(2, 2, 'Muharrir O‘qituvchi',  'editor@linguauz.test', '$2y$10$hznranNGmQgvcFbmCCP/o.TiKYCqyGYTF9ISmuqI2W13uQr5Zi80a'),
(3, 3, 'Namuna O‘quvchi',      'user@linguauz.test',   '$2y$10$Tv36oCd27QK4.N14Sz9bK.RvLsA92d1hQo916DI.wrU1FxSAZrDwu'),
(4, 3, 'Ikkinchi O‘quvchi',    'user2@linguauz.test',  '$2y$10$Tv36oCd27QK4.N14Sz9bK.RvLsA92d1hQo916DI.wrU1FxSAZrDwu');

INSERT INTO languages (id, code, name, native_name, speech_code) VALUES
(1, 'en', 'Ingliz tili', 'English',  'en-US'),
(2, 'de', 'Nemis tili',  'Deutsch',  'de-DE'),
(3, 'ru', 'Rus tili',    'Русский',  'ru-RU'),
(4, 'tr', 'Turk tili',   'Türkçe',   'tr-TR'),
(5, 'ko', 'Koreys tili', '한국어',     'ko-KR'),
(6, 'fr', 'Fransuz tili','Français', 'fr-FR');

INSERT INTO collections (id, language_id, owner_id, title, description, level, is_public, status) VALUES
(1, 1, 2, 'Kundalik so‘zlashuv', 'Salomlashish, oila, uy va kundalik harakatlarga oid eng ko‘p ishlatiladigan inglizcha so‘zlar.', 'A1', 1, 'active'),
(2, 1, 2, 'Sayohat va aeroport', 'Aeroport, mehmonxona va sayohat paytida kerak bo‘ladigan so‘z va iboralar.', 'A2', 1, 'active'),
(3, 1, 2, 'IT va dasturlash atamalari', 'Dasturchilar va kompyuter injiniringi talabalari uchun inglizcha kasbiy atamalar.', 'B1', 1, 'active'),
(4, 2, 2, 'Nemis tili: boshlang‘ich', 'Nemis tilini endi boshlayotganlar uchun asosiy so‘zlar va artikllar.', 'A1', 1, 'active'),
(5, 3, 2, 'Rus tili: asosiy fe’llar', 'Kundalik nutqda eng ko‘p uchraydigan ruscha fe’llar.', 'A1', 1, 'active'),
(6, 4, 1, 'Turk tili: salomlashish', 'Turkcha salomlashish va xushmuomalalik iboralari.', 'A1', 1, 'active'),
(7, 1, 3, 'Mening lug‘atim', 'Kitob o‘qiyotganda uchragan yangi so‘zlarim (shaxsiy to‘plam).', 'A2', 0, 'active'),
(8, 5, 4, 'Koreys tili: ilk qadamlar', 'Shaxsiy koreyscha lug‘at.', 'A1', 0, 'active');

INSERT INTO words (id, collection_id, created_by, term, translation, transcription, part_of_speech, example, example_translation) VALUES
-- 1-to‘plam: Kundalik so‘zlashuv (15)
(1, 1, 2, 'hello', 'salom', '/həˈləʊ/', 'phrase', 'Hello, how are you?', 'Salom, qalaysiz?'),
(2, 1, 2, 'goodbye', 'xayr', '/ˌɡʊdˈbaɪ/', 'phrase', 'Goodbye, see you tomorrow.', 'Xayr, ertaga ko‘rishguncha.'),
(3, 1, 2, 'thank you', 'rahmat', '/ˈθæŋk juː/', 'phrase', 'Thank you for your help.', 'Yordamingiz uchun rahmat.'),
(4, 1, 2, 'please', 'iltimos', '/pliːz/', 'adverb', 'Please sit down.', 'Iltimos, o‘tiring.'),
(5, 1, 2, 'family', 'oila', '/ˈfæməli/', 'noun', 'My family is big.', 'Mening oilam katta.'),
(6, 1, 2, 'friend', 'do‘st', '/frend/', 'noun', 'She is my best friend.', 'U mening eng yaqin do‘stim.'),
(7, 1, 2, 'house', 'uy', '/haʊs/', 'noun', 'This is my house.', 'Bu mening uyim.'),
(8, 1, 2, 'water', 'suv', '/ˈwɔːtə/', 'noun', 'Can I have some water?', 'Menga suv bera olasizmi?'),
(9, 1, 2, 'eat', 'yemoq', '/iːt/', 'verb', 'We eat breakfast at eight.', 'Biz soat sakkizda nonushta qilamiz.'),
(10, 1, 2, 'drink', 'ichmoq', '/drɪŋk/', 'verb', 'I drink tea every morning.', 'Men har kuni ertalab choy ichaman.'),
(11, 1, 2, 'go', 'bormoq', '/ɡəʊ/', 'verb', 'Let''s go to the park.', 'Keling, bog‘ga boramiz.'),
(12, 1, 2, 'big', 'katta', '/bɪɡ/', 'adjective', 'It is a big city.', 'Bu katta shahar.'),
(13, 1, 2, 'small', 'kichik', '/smɔːl/', 'adjective', 'I have a small car.', 'Mening kichik mashinam bor.'),
(14, 1, 2, 'today', 'bugun', '/təˈdeɪ/', 'adverb', 'Today is Monday.', 'Bugun dushanba.'),
(15, 1, 2, 'tomorrow', 'ertaga', '/təˈmɒrəʊ/', 'adverb', 'See you tomorrow.', 'Ertaga ko‘rishamiz.'),
-- 2-to‘plam: Sayohat (10)
(16, 2, 2, 'airport', 'aeroport', '/ˈeəpɔːt/', 'noun', 'The airport is far from here.', 'Aeroport bu yerdan uzoq.'),
(17, 2, 2, 'ticket', 'chipta', '/ˈtɪkɪt/', 'noun', 'I need a ticket to London.', 'Menga Londonga chipta kerak.'),
(18, 2, 2, 'passport', 'pasport', '/ˈpɑːspɔːt/', 'noun', 'Show your passport, please.', 'Pasportingizni ko‘rsating, iltimos.'),
(19, 2, 2, 'luggage', 'yuk, bagaj', '/ˈlʌɡɪdʒ/', 'noun', 'My luggage is heavy.', 'Mening yukim og‘ir.'),
(20, 2, 2, 'departure', 'jo‘nab ketish', '/dɪˈpɑːtʃə/', 'noun', 'The departure is at 10 a.m.', 'Jo‘nab ketish soat 10 da.'),
(21, 2, 2, 'arrival', 'yetib kelish', '/əˈraɪvl/', 'noun', 'Check the arrival time.', 'Yetib kelish vaqtini tekshiring.'),
(22, 2, 2, 'hotel', 'mehmonxona', '/həʊˈtel/', 'noun', 'We booked a hotel near the sea.', 'Biz dengiz yaqinidagi mehmonxonani band qildik.'),
(23, 2, 2, 'book a room', 'xona band qilmoq', NULL, 'phrase', 'I want to book a room for two nights.', 'Men ikki kechaga xona band qilmoqchiman.'),
(24, 2, 2, 'delay', 'kechikish', '/dɪˈleɪ/', 'noun', 'There is a two-hour delay.', 'Ikki soatlik kechikish bor.'),
(25, 2, 2, 'boarding pass', 'bortga chiqish taloni', NULL, 'phrase', 'Keep your boarding pass.', 'Bortga chiqish talonini saqlang.'),
-- 3-to‘plam: IT atamalari (10)
(26, 3, 2, 'software', 'dasturiy ta’minot', '/ˈsɒftweə/', 'noun', 'This software is free.', 'Bu dasturiy ta’minot bepul.'),
(27, 3, 2, 'database', 'ma’lumotlar bazasi', '/ˈdeɪtəbeɪs/', 'noun', 'The database stores user data.', 'Ma’lumotlar bazasi foydalanuvchi ma’lumotlarini saqlaydi.'),
(28, 3, 2, 'request', 'so‘rov', '/rɪˈkwest/', 'noun', 'The server received a GET request.', 'Server GET so‘rovini qabul qildi.'),
(29, 3, 2, 'deploy', 'joylashtirmoq', '/dɪˈplɔɪ/', 'verb', 'We deploy the app every Friday.', 'Biz ilovani har juma joylashtiramiz.'),
(30, 3, 2, 'debug', 'xatoni tuzatmoq', '/diːˈbʌɡ/', 'verb', 'I need to debug this function.', 'Men bu funksiyadagi xatoni tuzatishim kerak.'),
(31, 3, 2, 'framework', 'freymvork, karkas', '/ˈfreɪmwɜːk/', 'noun', 'Laravel is a PHP framework.', 'Laravel — PHP freymvorki.'),
(32, 3, 2, 'secure', 'xavfsiz', '/sɪˈkjʊə/', 'adjective', 'Use a secure connection.', 'Xavfsiz ulanishdan foydalaning.'),
(33, 3, 2, 'password', 'parol', '/ˈpɑːswɜːd/', 'noun', 'Never share your password.', 'Parolingizni hech kimga bermang.'),
(34, 3, 2, 'update', 'yangilamoq', '/ʌpˈdeɪt/', 'verb', 'Update your browser.', 'Brauzeringizni yangilang.'),
(35, 3, 2, 'network', 'tarmoq', '/ˈnetwɜːk/', 'noun', 'The local network is slow.', 'Lokal tarmoq sekin ishlayapti.'),
-- 4-to‘plam: Nemis tili (8)
(36, 4, 2, 'Hallo', 'salom', '[haˈloː]', 'phrase', 'Hallo, wie geht''s?', 'Salom, ishlar qalay?'),
(37, 4, 2, 'Danke', 'rahmat', '[ˈdaŋkə]', 'phrase', 'Danke schön!', 'Katta rahmat!'),
(38, 4, 2, 'das Buch', 'kitob', '[buːx]', 'noun', 'Das Buch ist interessant.', 'Kitob qiziqarli.'),
(39, 4, 2, 'die Schule', 'maktab', '[ˈʃuːlə]', 'noun', 'Die Schule beginnt um acht.', 'Maktab soat sakkizda boshlanadi.'),
(40, 4, 2, 'lernen', 'o‘rganmoq', '[ˈlɛʁnən]', 'verb', 'Ich lerne Deutsch.', 'Men nemis tilini o‘rganyapman.'),
(41, 4, 2, 'schön', 'chiroyli', '[ʃøːn]', 'adjective', 'Das Wetter ist schön.', 'Havo chiroyli.'),
(42, 4, 2, 'Guten Morgen', 'xayrli tong', NULL, 'phrase', 'Guten Morgen, Herr Müller!', 'Xayrli tong, janob Myuller!'),
(43, 4, 2, 'der Tisch', 'stol', '[tɪʃ]', 'noun', 'Der Tisch ist groß.', 'Stol katta.'),
-- 5-to‘plam: Rus fe’llari (8)
(44, 5, 2, 'говорить', 'gapirmoq', '[gəvɐˈrʲitʲ]', 'verb', 'Я говорю по-русски.', 'Men ruscha gapiraman.'),
(45, 5, 2, 'читать', 'o‘qimoq', '[t͡ɕɪˈtatʲ]', 'verb', 'Она читает книгу.', 'U kitob o‘qiyapti.'),
(46, 5, 2, 'писать', 'yozmoq', '[pʲɪˈsatʲ]', 'verb', 'Я пишу письмо.', 'Men xat yozyapman.'),
(47, 5, 2, 'знать', 'bilmoq', '[znatʲ]', 'verb', 'Я знаю ответ.', 'Men javobni bilaman.'),
(48, 5, 2, 'работать', 'ishlamoq', '[rɐˈbotətʲ]', 'verb', 'Он работает в банке.', 'U bankda ishlaydi.'),
(49, 5, 2, 'жить', 'yashamoq', '[ʐɨtʲ]', 'verb', 'Мы живём в Андижане.', 'Biz Andijonda yashaymiz.'),
(50, 5, 2, 'понимать', 'tushunmoq', '[pənʲɪˈmatʲ]', 'verb', 'Я вас понимаю.', 'Men sizni tushunaman.'),
(51, 5, 2, 'любить', 'sevmoq, yaxshi ko‘rmoq', '[lʲʊˈbʲitʲ]', 'verb', 'Я люблю музыку.', 'Men musiqani yaxshi ko‘raman.'),
-- 6-to‘plam: Turk tili (6)
(52, 6, 1, 'Merhaba', 'salom', NULL, 'phrase', 'Merhaba, nasılsın?', 'Salom, qalaysan?'),
(53, 6, 1, 'Günaydın', 'xayrli tong', NULL, 'phrase', 'Günaydın, anne!', 'Xayrli tong, oyijon!'),
(54, 6, 1, 'Teşekkür ederim', 'rahmat', NULL, 'phrase', 'Yardımın için teşekkür ederim.', 'Yordaming uchun rahmat.'),
(55, 6, 1, 'Nasılsın?', 'qalaysan?', NULL, 'phrase', 'Merhaba Ali, nasılsın?', 'Salom Ali, qalaysan?'),
(56, 6, 1, 'Hoşça kal', 'xayr', NULL, 'phrase', 'Hoşça kal, yarın görüşürüz.', 'Xayr, ertaga ko‘rishamiz.'),
(57, 6, 1, 'Evet', 'ha', NULL, 'other', 'Evet, anladım.', 'Ha, tushundim.'),
-- 7-to‘plam: o‘quvchining shaxsiy lug‘ati (4)
(58, 7, 3, 'achieve', 'erishmoq', '/əˈtʃiːv/', 'verb', 'You can achieve your goals.', 'Siz maqsadlaringizga erisha olasiz.'),
(59, 7, 3, 'improve', 'yaxshilamoq', '/ɪmˈpruːv/', 'verb', 'I want to improve my English.', 'Men ingliz tilimni yaxshilamoqchiman.'),
(60, 7, 3, 'schedule', 'jadval', '/ˈʃedjuːl/', 'noun', 'Check the class schedule.', 'Dars jadvalini tekshiring.'),
(61, 7, 3, 'knowledge', 'bilim', '/ˈnɒlɪdʒ/', 'noun', 'Knowledge is power.', 'Bilim — kuch.'),
-- 8-to‘plam: ikkinchi o‘quvchining shaxsiy lug‘ati (3)
(62, 8, 4, '안녕하세요', 'salom', '[annyeonghaseyo]', 'phrase', '안녕하세요, 만나서 반갑습니다.', 'Salom, tanishganimdan xursandman.'),
(63, 8, 4, '감사합니다', 'rahmat', '[gamsahamnida]', 'phrase', '도와주셔서 감사합니다.', 'Yordamingiz uchun rahmat.'),
(64, 8, 4, '사랑', 'sevgi', '[sarang]', 'noun', '사랑은 아름다워요.', 'Sevgi go‘zal.');

INSERT INTO exercises (id, collection_id, created_by, title, type, direction, question_count) VALUES
(1, 1, 2, 'Kundalik so‘zlar: variantli test', 'choice', 'to_uz', 10),
(2, 1, 2, 'Kundalik so‘zlar: yozma tarjima', 'typing', 'to_foreign', 8),
(3, 2, 2, 'Sayohat lug‘ati testi', 'choice', 'to_uz', 8),
(4, 3, 2, 'IT atamalari: tez test', 'choice', 'to_uz', 10),
(5, 4, 2, 'Nemischa so‘zlar', 'choice', 'to_uz', 6),
(6, 5, 2, 'Rus fe’llari: yozma javob', 'typing', 'to_uz', 6);

-- O‘quvchi (id=3) "Kundalik so‘zlashuv" to‘plamini o‘rganmoqda: turli holatdagi takrorlash rejasi
INSERT INTO review_schedules (user_id, word_id, repetitions, interval_days, ease_factor, due_date, state, last_reviewed_at, version) VALUES
(3, 1, 4, 24, 2.70, DATE_ADD(CURDATE(), INTERVAL 12 DAY), 'mastered', DATE_SUB(NOW(), INTERVAL 12 DAY), 4),
(3, 2, 3, 15, 2.60, DATE_ADD(CURDATE(), INTERVAL 6 DAY),  'review',   DATE_SUB(NOW(), INTERVAL 9 DAY), 3),
(3, 3, 3, 14, 2.50, DATE_ADD(CURDATE(), INTERVAL 3 DAY),  'review',   DATE_SUB(NOW(), INTERVAL 11 DAY), 3),
(3, 4, 2, 6,  2.36, DATE_ADD(CURDATE(), INTERVAL 1 DAY),  'review',   DATE_SUB(NOW(), INTERVAL 5 DAY), 2),
(3, 5, 2, 6,  2.50, CURDATE(),                            'review',   DATE_SUB(NOW(), INTERVAL 6 DAY), 2),
(3, 6, 1, 1,  2.36, DATE_SUB(CURDATE(), INTERVAL 1 DAY),  'learning', DATE_SUB(NOW(), INTERVAL 2 DAY), 1),
(3, 7, 0, 1,  2.18, CURDATE(),                            'learning', DATE_SUB(NOW(), INTERVAL 1 DAY), 1),
(3, 8, 1, 1,  2.50, DATE_SUB(CURDATE(), INTERVAL 2 DAY),  'learning', DATE_SUB(NOW(), INTERVAL 3 DAY), 1),
(3, 9, 2, 6,  2.60, DATE_ADD(CURDATE(), INTERVAL 2 DAY),  'review',   DATE_SUB(NOW(), INTERVAL 4 DAY), 2),
(3, 10, 0, 0, 2.50, CURDATE(), 'new', NULL, 0),
(3, 11, 0, 0, 2.50, CURDATE(), 'new', NULL, 0),
(3, 12, 0, 0, 2.50, CURDATE(), 'new', NULL, 0),
(3, 13, 0, 0, 2.50, CURDATE(), 'new', NULL, 0),
(3, 14, 0, 0, 2.50, CURDATE(), 'new', NULL, 0),
(3, 15, 0, 0, 2.50, CURDATE(), 'new', NULL, 0),
(3, 58, 1, 1, 2.50, CURDATE(), 'learning', DATE_SUB(NOW(), INTERVAL 1 DAY), 1),
(3, 59, 0, 0, 2.50, CURDATE(), 'new', NULL, 0),
-- Ikkinchi o‘quvchi (id=4) o‘z koreyscha to‘plamini o‘rganmoqda
(4, 62, 0, 0, 2.50, CURDATE(), 'new', NULL, 0),
(4, 63, 0, 0, 2.50, CURDATE(), 'new', NULL, 0),
(4, 64, 0, 0, 2.50, CURDATE(), 'new', NULL, 0);

INSERT INTO review_logs (schedule_id, user_id, word_id, quality, prev_interval, new_interval, prev_due, new_due, reviewed_at) VALUES
(1, 3, 1, 5, 15, 24, DATE_SUB(CURDATE(), INTERVAL 12 DAY), DATE_ADD(CURDATE(), INTERVAL 12 DAY), DATE_SUB(NOW(), INTERVAL 12 DAY)),
(2, 3, 2, 4, 6, 15, DATE_SUB(CURDATE(), INTERVAL 9 DAY), DATE_ADD(CURDATE(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 9 DAY)),
(4, 3, 4, 3, 1, 6, DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(6, 3, 6, 4, 0, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), DATE_SUB(CURDATE(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(7, 3, 7, 1, 1, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), CURDATE(), DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO exercise_attempts (id, exercise_id, user_id, score, total, percent, started_at, finished_at) VALUES
(1, 5, 3, 5, 6, 83.33, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY) + INTERVAL 3 MINUTE);

INSERT INTO attempt_answers (attempt_id, word_id, prompt, expected, given_answer, is_correct) VALUES
(1, 36, 'Hallo', 'salom', 'salom', 1),
(1, 37, 'Danke', 'rahmat', 'rahmat', 1),
(1, 38, 'das Buch', 'kitob', 'kitob', 1),
(1, 40, 'lernen', 'o‘rganmoq', 'o‘rganmoq', 1),
(1, 41, 'schön', 'chiroyli', 'katta', 0),
(1, 43, 'der Tisch', 'stol', 'stol', 1);

INSERT INTO contact_messages (name, email, message, ip, mail_status, mail_error, created_at) VALUES
('Sardor', 'sardor@example.com', 'Ispan tili uchun ham to‘plamlar qo‘shasizmi?', '127.0.0.1', 'sent', NULL, DATE_SUB(NOW(), INTERVAL 3 DAY)),
('Nilufar', 'nilufar@example.com', 'Takrorlash rejasi juda qulay ekan, rahmat!', '127.0.0.1', 'failed', 'SMTP serverga ulanib bo‘lmadi (namunaviy yozuv)', DATE_SUB(NOW(), INTERVAL 1 DAY));
