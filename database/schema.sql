-- =====================================================================
-- LinguaUz — xorijiy tillarni o‘rganish platformasi
-- Baza tuzilmasi (MySQL 8 / MariaDB 10.4+), kodlash: utf8mb4
-- O‘rnatish:  mysql -u root -p linguauz < database/schema.sql
-- =====================================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS review_logs, review_schedules, attempt_answers, exercise_attempts, exercises,
    files, words, collections, languages, contact_messages, login_attempts, users, roles;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Yordamchi jadvallar: rollar va foydalanuvchilar
-- ---------------------------------------------------------------------
CREATE TABLE roles (
    id    TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug  VARCHAR(20)  NOT NULL,
    name  VARCHAR(50)  NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    role_id       TINYINT UNSIGNED NOT NULL,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role (role_id),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE login_attempts (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    email        VARCHAR(150) NOT NULL,
    ip           VARCHAR(45)  NOT NULL,
    attempted_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_login_ip (ip, attempted_at),
    KEY idx_login_email (email, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Predmet jadvallari
-- ---------------------------------------------------------------------
-- Tillar (ma’lumotnoma)
CREATE TABLE languages (
    id          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    code        VARCHAR(10) NOT NULL,              -- ISO kod: en, de, ru
    name        VARCHAR(60) NOT NULL,              -- Ingliz tili
    native_name VARCHAR(60) NOT NULL,              -- English
    speech_code VARCHAR(10) NOT NULL,              -- talaffuz uchun: en-US
    created_at  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_languages_code (code),
    UNIQUE KEY uq_languages_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- So‘z to‘plamlari
CREATE TABLE collections (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    language_id SMALLINT UNSIGNED NOT NULL,
    owner_id    INT UNSIGNED NOT NULL,
    title       VARCHAR(150) NOT NULL,
    description TEXT NULL,
    level       ENUM('A1','A2','B1','B2','C1','C2') NOT NULL DEFAULT 'A1',
    is_public   TINYINT(1) NOT NULL DEFAULT 0,
    status      ENUM('active','archived') NOT NULL DEFAULT 'active',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_collections_owner_title (owner_id, title),
    KEY idx_collections_language (language_id),
    KEY idx_collections_visibility (status, is_public),
    CONSTRAINT fk_collections_language FOREIGN KEY (language_id) REFERENCES languages (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_collections_owner FOREIGN KEY (owner_id) REFERENCES users (id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- So‘zlar (asosiy predmet jadvali)
CREATE TABLE words (
    id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    collection_id       INT UNSIGNED NOT NULL,
    created_by          INT UNSIGNED NOT NULL,
    term                VARCHAR(120) NOT NULL,
    translation         VARCHAR(200) NOT NULL,
    transcription       VARCHAR(120) NULL,
    part_of_speech      ENUM('noun','verb','adjective','adverb','phrase','other') NOT NULL DEFAULT 'noun',
    example             VARCHAR(500) NULL,
    example_translation VARCHAR(500) NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_words_collection_term (collection_id, term),
    KEY idx_words_term (term),
    KEY idx_words_translation (translation),
    KEY idx_words_creator (created_by),
    CONSTRAINT fk_words_collection FOREIGN KEY (collection_id) REFERENCES collections (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_words_creator FOREIGN KEY (created_by) REFERENCES users (id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mashqlar (test shablonlari)
CREATE TABLE exercises (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    collection_id  INT UNSIGNED NOT NULL,
    created_by     INT UNSIGNED NOT NULL,
    title          VARCHAR(150) NOT NULL,
    type           ENUM('choice','typing') NOT NULL DEFAULT 'choice',
    direction      ENUM('to_uz','to_foreign') NOT NULL DEFAULT 'to_uz',
    question_count TINYINT UNSIGNED NOT NULL DEFAULT 10,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_exercises_collection (collection_id),
    KEY idx_exercises_creator (created_by),
    CONSTRAINT fk_exercises_collection FOREIGN KEY (collection_id) REFERENCES collections (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_exercises_creator FOREIGN KEY (created_by) REFERENCES users (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_exercises_count CHECK (question_count BETWEEN 3 AND 30)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mashq urinishlari va javoblari
CREATE TABLE exercise_attempts (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    exercise_id INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    score       SMALLINT UNSIGNED NOT NULL,
    total       SMALLINT UNSIGNED NOT NULL,
    percent     DECIMAL(5,2) NOT NULL,
    started_at  DATETIME NOT NULL,
    finished_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_attempts_user (user_id, finished_at),
    KEY idx_attempts_exercise (exercise_id),
    CONSTRAINT fk_attempts_exercise FOREIGN KEY (exercise_id) REFERENCES exercises (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_attempts_user FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE attempt_answers (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    attempt_id   INT UNSIGNED NOT NULL,
    word_id      INT UNSIGNED NOT NULL,
    prompt       VARCHAR(200) NOT NULL,
    expected     VARCHAR(200) NOT NULL,
    given_answer VARCHAR(200) NOT NULL DEFAULT '',
    is_correct   TINYINT(1) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_answers_attempt (attempt_id),
    KEY idx_answers_word (word_id),
    CONSTRAINT fk_answers_attempt FOREIGN KEY (attempt_id) REFERENCES exercise_attempts (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_answers_word FOREIGN KEY (word_id) REFERENCES words (id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Takrorlash rejasi (SM-2 intervalli takrorlash algoritmi holati)
CREATE TABLE review_schedules (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id          INT UNSIGNED NOT NULL,
    word_id          INT UNSIGNED NOT NULL,
    repetitions      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    interval_days    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    ease_factor      DECIMAL(4,2) NOT NULL DEFAULT 2.50,
    due_date         DATE NOT NULL,
    state            ENUM('new','learning','review','mastered') NOT NULL DEFAULT 'new',
    last_reviewed_at DATETIME NULL,
    version          INT UNSIGNED NOT NULL DEFAULT 0,   -- takroriy so‘rovni aniqlash uchun
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_schedule_user_word (user_id, word_id),
    KEY idx_schedule_due (user_id, due_date),
    KEY idx_schedule_word (word_id),
    CONSTRAINT fk_schedule_user FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_schedule_word FOREIGN KEY (word_id) REFERENCES words (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT chk_schedule_ease CHECK (ease_factor BETWEEN 1.30 AND 5.00)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE review_logs (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    schedule_id  INT UNSIGNED NOT NULL,
    user_id      INT UNSIGNED NOT NULL,
    word_id      INT UNSIGNED NOT NULL,
    quality      TINYINT UNSIGNED NOT NULL,             -- 1 qayta, 3 qiyin, 4 yaxshi, 5 oson
    prev_interval SMALLINT UNSIGNED NOT NULL,
    new_interval SMALLINT UNSIGNED NOT NULL,
    prev_due     DATE NOT NULL,
    new_due      DATE NOT NULL,
    reviewed_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_logs_user (user_id, reviewed_at),
    KEY idx_logs_schedule (schedule_id),
    CONSTRAINT fk_logs_schedule FOREIGN KEY (schedule_id) REFERENCES review_schedules (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_logs_user FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_logs_word FOREIGN KEY (word_id) REFERENCES words (id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Fayllar metama’lumotlari va aloqa xabarlari
-- ---------------------------------------------------------------------
CREATE TABLE files (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    collection_id INT UNSIGNED NOT NULL,
    uploader_id   INT UNSIGNED NOT NULL,
    category      ENUM('image','pdf','video') NOT NULL,
    original_name VARCHAR(200) NOT NULL,
    stored_name   VARCHAR(64)  NOT NULL,
    mime          VARCHAR(100) NOT NULL,
    size_bytes    INT UNSIGNED NOT NULL,
    description   VARCHAR(255) NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_files_stored (stored_name),
    KEY idx_files_collection (collection_id),
    KEY idx_files_name (original_name),
    CONSTRAINT fk_files_collection FOREIGN KEY (collection_id) REFERENCES collections (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_files_uploader FOREIGN KEY (uploader_id) REFERENCES users (id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contact_messages (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    message     TEXT NOT NULL,
    ip          VARCHAR(45)  NOT NULL,
    mail_status ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
    mail_error  VARCHAR(255) NULL,
    is_read     TINYINT(1) NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_contact_ip (ip, created_at),
    KEY idx_contact_status (mail_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
