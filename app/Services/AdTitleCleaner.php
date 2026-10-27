<?php

namespace App\Services;

/**
 * Чистка назв оголошень кодом (замість переписування через AI, ads:seo-optimize):
 * прибирає телефони, посилання, email, емодзі й декоративні символи, повтори «!!!»,
 * а НАЗВУ ВЕЛИКИМИ ЛІТЕРАМИ переводить у звичайну (короткі абревіатури — BMW, USB, LED — лишаються).
 * Суть назви не змінюється; якщо після чистки лишається менше 3 символів — повертається оригінал.
 */
class AdTitleCleaner
{
    public static function clean(string $title): string
    {
        $t = $title;

        // Посилання й email
        $t = preg_replace('~(https?://\S+|www\.\S+)~iu', ' ', $t);
        $t = preg_replace('~\b[\w.+-]+@[\w-]+\.[\w.]{2,}\b~u', ' ', $t);
        $t = preg_replace('~\b[a-z0-9-]{2,}\.(com\.ua|com|ua|net|org|biz|info|shop|store|ru)\b\S*~iu', ' ', $t);

        // Телефони: 10–12 цифр з пробілами/дефісами/дужками (+38 067 123 45 67, (067)1234567)
        $t = preg_replace_callback('~\+?\(?\d[\d\s\-\(\)]{8,}\d~u', function ($m) {
            $digits = preg_replace('~\D~', '', $m[0]);
            return (strlen($digits) >= 10 && strlen($digits) <= 12) ? ' ' : $m[0];
        }, $t);
        $t = preg_replace('~\b(тел\.?|телефон|звоните|дзвоніть|viber|вайбер|telegram|телеграм)\s*[:.]?\s*$~iu', ' ', trim($t));

        // Емодзі й декоративні символи
        $t = preg_replace('~[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{FE0F}\x{200D}]~u', '', $t);
        $t = preg_replace('~(?![№°])\p{So}~u', '', $t);

        // Повтори розділових знаків
        $t = preg_replace(['~!{2,}~u', '~\?{2,}~u', '~\.{4,}~u', '~-{2,}~u', '~\*{2,}~u', '~_{2,}~u', '~={2,}~u'], ['!', '?', '…', '-', '', '', ''], $t);

        // ВЕЛИКІ ЛІТЕРИ → звичайні
        $t = self::fixCaps($t);

        // Пробіли й «хвости» з розділових знаків
        $t = preg_replace('~\s{2,}~u', ' ', $t);
        $t = preg_replace('~\s+([,.!?;:])~u', '$1', $t);
        $t = trim($t, " \t\n\r\0\x0B,;:-–—|/*");
        // «…пишіть на», «…звоніть по» після вирізаного контакту
        $t = preg_replace('~(\s+(на|по|за|в|у|пишіть|пишите|звоните|дзвоніть|звоніть|тел\.?|телефон|контакти|контакты|e-?mail|viber|вайбер))+\s*$~iu', '', $t);
        $t = trim($t, " \t\n\r\0\x0B,;:-–—|/*");

        return mb_strlen($t) >= 3 ? $t : $title;
    }

    protected static function fixCaps(string $t): string
    {
        $letters = preg_replace('~[^\p{L}]~u', '', $t);
        $len = mb_strlen($letters);
        if ($len < 8) {
            return $t;
        }
        $upper = mb_strlen(preg_replace('~[^\p{Lu}]~u', '', $letters));
        if ($upper / $len < 0.7) {
            return $t;
        }

        $t = preg_replace_callback('~[\p{L}\d][\p{L}\d\'’-]*~u', function ($m) {
            $w = $m[0];
            // Короткі латинські абревіатури й моделі (BMW, USB, LED, X5, 256GB) лишаються
            if (preg_match('~^[A-Z0-9-]+$~', $w) && (mb_strlen($w) <= 4 || preg_match('~\d~', $w))) {
                return $w;
            }
            return mb_strtolower($w);
        }, $t);

        return mb_strtoupper(mb_substr($t, 0, 1)) . mb_substr($t, 1);
    }
}
