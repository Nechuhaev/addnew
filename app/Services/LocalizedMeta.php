<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

/**
 * SEO-тексти сторінки (title, description, текст) з урахуванням мови.
 *
 * Порядок для української версії: власний UK-текст сутності → UK-шаблон
 * із «SEO тексти» → власний RU-текст → RU-шаблон. Раніше бралось
 * «власний текст (з фолбеком на RU) ?? шаблон», тож якщо в міста/області
 * був заповнений лише RU-заголовок, на UK-сторінці показувався російський
 * текст, хоча український шаблон існував. Порожній рядок вважається
 * незаповненим (через «??» сторінка могла лишитись без title).
 */
class LocalizedMeta
{
    const MAP = [
        'meta_title' => 'meta_title',
        'meta_description' => 'meta_description',
        'description' => 'content', // поле сутності, з якого береться текст
    ];

    /**
     * @param Model|null $entity  AdCity / AdRegion / AdCategory / AdTag …
     * @param Model|null $seo     App\SeoField
     * @param array      $values  підстановки ---…--- для strtr
     * @return array ['meta_title' => …, 'meta_description' => …, 'description' => …]
     *               (null, якщо тексту немає ніде — шаблони підставляють свій запасний варіант через «??»)
     */
    public static function build(?Model $entity, ?Model $seo, array $values = []): array
    {
        $out = [];
        foreach (self::MAP as $key => $entityField) {
            $out[$key] = self::pick($entity, $entityField, $seo, $key, $values);
        }
        return $out;
    }

    public static function pick(?Model $entity, string $entityField, ?Model $seo, string $seoField, array $values = []): ?string
    {
        $raw = function (?Model $m, string $field) {
            if (!$m) {
                return '';
            }
            $attrs = $m->getAttributes();
            return trim((string) ($attrs[$field] ?? ''));
        };

        $candidates = app()->getLocale() === 'uk'
            ? [$raw($entity, $entityField . '_uk'), $raw($seo, $seoField . '_uk'), $raw($entity, $entityField), $raw($seo, $seoField)]
            : [$raw($entity, $entityField), $raw($seo, $seoField)];

        foreach ($candidates as $text) {
            if ($text !== '') {
                return strtr($text, $values);
            }
        }
        return null;
    }
}
