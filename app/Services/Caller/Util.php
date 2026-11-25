<?php

namespace App\Services\Caller;

class Util
{
    /** Нормалізує український номер до E.164 (+380XXXXXXXXX) або повертає null. */
    public static function normalizeUaPhone(string $raw): ?string
    {
        $d = preg_replace('/\D+/', '', $raw);
        if (strpos($d, '00') === 0) {
            $d = substr($d, 2);
        }
        if (strlen($d) === 10 && $d[0] === '0') {
            $d = '38' . $d;          // 0671234567
        } elseif (strlen($d) === 9) {
            $d = '380' . $d;         // 671234567
        } elseif (strlen($d) === 11 && strpos($d, '80') === 0) {
            $d = '3' . $d;           // 80671234567
        }
        return preg_match('/^380\d{9}$/', $d) ? '+' . $d : null;
    }

    /** Домен сайту без протоколу, www і шляху: «https://www.Shop.ua/catalog» → «shop.ua». */
    public static function domain(?string $url): string
    {
        $u = mb_strtolower(trim((string) $url));
        $u = preg_replace('#^[a-z]+://#', '', $u);
        $u = preg_replace('#^www\.#', '', $u);
        $u = explode('/', $u)[0];
        $u = explode('?', $u)[0];
        return $u;
    }

    public static function isValidEmail(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL)
            && preg_match('/\.[a-z]{2,}$/i', $email)
            && strpos($email, '..') === false;
    }

    /**
     * Підпис вебхука ElevenLabs: «t=<unix>,v0=<hex hmac_sha256(secret, "<t>.<body>")>».
     * Та сама схема, що й у webhooks.constructEvent офіційних SDK; допуск за часом 30 хв.
     */
    public static function verifyElevenLabsSignature(string $rawBody, ?string $header, ?string $secret, ?int $now = null): bool
    {
        if (empty($header) || empty($secret)) {
            return false;
        }
        $t = null;
        $sig = null;
        foreach (explode(',', $header) as $part) {
            $part = trim($part);
            if (strpos($part, 't=') === 0) {
                $t = substr($part, 2);
            } elseif (strpos($part, 'v0=') === 0) {
                $sig = $part;
            }
        }
        if ($t === null || $sig === null || !ctype_digit($t)) {
            return false;
        }
        $now = $now ?? time();
        if ((int) $t < $now - 30 * 60) {
            return false;
        }
        $expected = 'v0=' . hash_hmac('sha256', $t . '.' . $rawBody, $secret);
        return hash_equals($expected, $sig);
    }
}
