<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Fayl yuklash xavfsizligi:
 *  - hajm, kengaytma va server aniqlagan MIME turi tekshiriladi;
 *  - saqlash nomi tasodifiy yaratiladi, asl nom faqat ma’lumot sifatida saqlanadi;
 *  - fayllar public/ dan tashqarida (storage/uploads) saqlanadi.
 */
final class Uploader
{
    public const RULES = [
        'image' => ['ext' => ['jpg', 'jpeg', 'png', 'webp'], 'mime' => ['image/jpeg', 'image/png', 'image/webp'], 'max' => 2 * 1024 * 1024, 'label' => 'Rasm'],
        'pdf'   => ['ext' => ['pdf'], 'mime' => ['application/pdf'], 'max' => 5 * 1024 * 1024, 'label' => 'PDF hujjat'],
        'video' => ['ext' => ['mp4'], 'mime' => ['video/mp4'], 'max' => 20 * 1024 * 1024, 'label' => 'Video'],
    ];

    private const EXT_MIME = [
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp',
        'pdf' => 'application/pdf', 'mp4' => 'video/mp4',
    ];

    public static function directory(): string
    {
        return BASE_PATH . '/storage/uploads';
    }

    public static function path(string $storedName): string
    {
        // Saqlash nomi faqat [a-f0-9].ext ko‘rinishida — katalogdan chiqib ketish imkoni yo‘q
        if (!preg_match('/^[a-f0-9]{32}\.(jpg|png|webp|pdf|mp4)$/', $storedName)) {
            throw new \RuntimeException('Noto‘g‘ri saqlash nomi.');
        }
        return self::directory() . '/' . $storedName;
    }

    /**
     * Faylni tekshiradi. Xato bo‘lsa matnli xabar, aks holda meta-ma’lumot qaytaradi.
     * @return array{ok:bool,error?:string,category?:string,ext?:string,mime?:string,size?:int,original?:string}
     */
    public static function validate(mixed $file): array
    {
        if (!is_array($file) || !isset($file['error']) || is_array($file['error'])) {
            return ['ok' => false, 'error' => 'Fayl tanlanmagan.'];
        }
        switch ((int) $file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return ['ok' => false, 'error' => 'Fayl tanlanmagan.'];
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['ok' => false, 'error' => 'Fayl hajmi server chegarasidan katta.'];
            default:
                return ['ok' => false, 'error' => 'Faylni yuklashda xatolik yuz berdi (kod ' . (int) $file['error'] . ').'];
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            return ['ok' => false, 'error' => 'Fayl HTTP yuklash orqali kelmagan.'];
        }

        $original = mb_substr(basename(str_replace('\\', '/', (string) $file['name'])), 0, 200);
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $category = null;
        foreach (self::RULES as $cat => $rule) {
            if (in_array($ext, $rule['ext'], true)) {
                $category = $cat;
                break;
            }
        }
        if ($category === null) {
            return ['ok' => false, 'error' => 'Ruxsat etilmagan fayl turi. Faqat JPG, PNG, WEBP, PDF yoki MP4 qabul qilinadi.'];
        }

        $size = (int) filesize($file['tmp_name']);
        $rule = self::RULES[$category];
        if ($size <= 0) {
            return ['ok' => false, 'error' => 'Fayl bo‘sh.'];
        }
        if ($size > $rule['max']) {
            return ['ok' => false, 'error' => sprintf('%s hajmi %s dan oshmasligi kerak (yuborilgan: %s).', $rule['label'], human_size($rule['max']), human_size($size))];
        }

        // Server aniqlagan MIME turi (brauzer yuborgan turga ishonilmaydi)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($file['tmp_name']);
        if (!in_array($mime, $rule['mime'], true) || self::EXT_MIME[$ext] !== $mime) {
            return ['ok' => false, 'error' => 'Fayl mazmuni kengaytmaga mos emas (aniqlangan tur: ' . $mime . ').'];
        }
        if ($category === 'image' && @getimagesize($file['tmp_name']) === false) {
            return ['ok' => false, 'error' => 'Rasm fayli buzilgan yoki haqiqiy rasm emas.'];
        }

        return ['ok' => true, 'category' => $category, 'ext' => $ext === 'jpeg' ? 'jpg' : $ext, 'mime' => $mime, 'size' => $size, 'original' => $original];
    }

    /** Faylni tasodifiy nom bilan saqlaydi va saqlash nomini qaytaradi */
    public static function store(string $tmpName, string $ext): string
    {
        $dir = self::directory();
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        $stored = bin2hex(random_bytes(16)) . '.' . $ext;
        if (!move_uploaded_file($tmpName, $dir . '/' . $stored)) {
            throw new \RuntimeException('Faylni saqlab bo‘lmadi.');
        }
        @chmod($dir . '/' . $stored, 0640);
        return $stored;
    }

    public static function remove(?string $storedName): void
    {
        if (!$storedName) {
            return;
        }
        try {
            $path = self::path($storedName);
        } catch (\RuntimeException) {
            return;
        }
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
