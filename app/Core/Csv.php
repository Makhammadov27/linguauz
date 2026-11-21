<?php
declare(strict_types=1);

namespace App\Core;

/**
 * CSV eksport: UTF-8 (BOM bilan — Excel o‘zbekcha harflarni to‘g‘ri ochadi),
 * RFC 4180 qo‘shtirnoq qoidalari va formula inyeksiyasidan himoya.
 */
final class Csv
{
    public static function download(string $filename, array $headers, iterable $rows): never
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $filename) . '"');
        header('Cache-Control: no-store');

        $out = fopen('php://output', 'wb');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $headers, ',', '"', '', "\r\n");
        foreach ($rows as $row) {
            fputcsv($out, array_map([self::class, 'safe'], $row), ',', '"', '', "\r\n");
        }
        fclose($out);
        exit;
    }

    /** =, +, -, @ bilan boshlangan qiymat elektron jadvalda formula bo‘lib bajarilmasligi uchun */
    public static function safe(mixed $value): string
    {
        $s = (string) ($value ?? '');
        if ($s !== '' && in_array($s[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $s;
        }
        return $s;
    }
}
