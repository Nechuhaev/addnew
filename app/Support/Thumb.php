<?php

namespace App\Support;

/**
 * Зменшені копії фото з нашого S3 (Front\ThumbController): /thumbs/{ширина}/s3/{шлях}.
 * Перший запит генерує файл у public/thumbs, далі його віддає вебсервер без PHP.
 * Чужі посилання (не наш бакет) повертаються як є.
 */
class Thumb
{
    const WIDTHS = [320, 640, 960];

    /** Шлях до фото в бакеті або null, якщо фото не з нашого S3 / шлях небезпечний */
    public static function s3Path(?string $src): ?string
    {
        $src = trim((string) $src);
        $bucket = preg_quote(static::bucket(), '~');
        if ($src === '' || !preg_match('~^https?://' . $bucket . '\.s3(?:[.-][a-z0-9-]+)?\.amazonaws\.com/([^?#]+)$~i', $src, $m)) {
            return null;
        }
        $path = $m[1];
        if (strpos($path, '..') !== false || !preg_match('~^[A-Za-z0-9._\-/]+\.(jpe?g|png|gif|webp|bmp)$~i', $path)) {
            return null;
        }

        return $path;
    }

    /** Без GD копії не зробити — тоді показуємо оригінали (інакше кожне фото = запит у PHP + редирект) */
    public static function enabled(): bool
    {
        static $on = null;

        return $on ?? ($on = function_exists('imagecreatefromstring') && function_exists('imagejpeg')
            && (bool) config('services.thumbs.enabled', true));
    }

    public static function url(?string $src, int $width): string
    {
        $path = static::enabled() ? static::s3Path($src) : null;
        if ($path === null || !in_array($width, self::WIDTHS, true)) {
            return (string) $src;
        }
        // Копія завжди JPEG: ім'я з .jpg, щоб вебсервер віддавав правильний тип
        $suffix = preg_match('~\.jpe?g$~i', $path) ? '' : '.jpg';

        return '/thumbs/' . $width . '/s3/' . $path . $suffix;
    }

    /** srcset для картки фіксованого розміру: 1x і 2x */
    public static function srcset(?string $src, int $width1x): string
    {
        if (!static::enabled() || static::s3Path($src) === null) {
            return '';
        }

        return static::url($src, $width1x) . ' 1x, ' . static::url($src, $width1x * 2) . ' 2x';
    }

    /** srcset з шириною (для фото на всю ширину екрана на мобільних) */
    public static function srcsetW(?string $src, array $widths): string
    {
        if (!static::enabled() || static::s3Path($src) === null) {
            return '';
        }

        return implode(', ', array_map(function ($w) use ($src) {
            return static::url($src, $w) . ' ' . $w . 'w';
        }, $widths));
    }

    /** Адреса оригіналу за шляхом із адреси копії (зворотне до url()) */
    public static function sourceUrl(string $thumbPath): string
    {
        $path = preg_replace('~\.(png|gif|webp|bmp|jpeg)\.jpg$~i', '.$1', $thumbPath);

        return 'https://' . static::bucket() . '.s3.amazonaws.com/' . $path;
    }

    public static function bucket(): string
    {
        return (string) (config('filesystems.disks.s3.bucket') ?: 'addnew-wp');
    }
}
