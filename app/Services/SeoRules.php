<?php

namespace App\Services;

/**
 * Правила індексації гео-сторінок (config/seo.php).
 */
class SeoRules
{
    /** Слаги країн, чиї гео-сторінки закриті noindex і не йдуть у sitemap */
    public static function noindexCountries(): array
    {
        return (array) config('seo.noindex_countries', ['rossiya']);
    }

    public static function isNoindexCountry(?string $countrySlug): bool
    {
        return $countrySlug !== null && in_array($countrySlug, self::noindexCountries(), true);
    }

    /** Слаги чутливих категорій (config/seo.php) */
    public static function noindexCategorySlugs(): array
    {
        return (array) config('seo.noindex_categories', ['znakomstva-i-kontaktyi']);
    }

    /**
     * id чутливих категорій разом із підкатегоріями (кеш 1 год).
     */
    public static function sensitiveCategoryIds(): array
    {
        $slugs = self::noindexCategorySlugs();
        if (!$slugs) {
            return [];
        }

        return \Illuminate\Support\Facades\Cache::remember('seo_sensitive_category_ids_' . md5(implode(',', $slugs)), 3600, function () use ($slugs) {
            $ids = \App\AdCategory::whereIn('slug', $slugs)->pluck('id')->all();
            $children = $ids ? \App\AdCategory::whereIn('parent_id', $ids)->pluck('id')->all() : [];

            return array_values(array_unique(array_merge($ids, $children)));
        });
    }

    public static function isSensitiveCategory($category): bool
    {
        return $category && in_array((int) $category->id, self::sensitiveCategoryIds(), true);
    }

    /**
     * Оголошення в чутливій категорії або з noindex-стоп-словом у назві чи тексті.
     */
    public static function isSensitiveAd($ad): bool
    {
        if (!$ad) {
            return false;
        }
        $raw = method_exists($ad, 'getAttributes') ? $ad->getAttributes() : (array) $ad;   // модель або рядок DB::table
        if (in_array((int) ($raw['category_id'] ?? 0), self::sensitiveCategoryIds(), true)) {
            return true;
        }

        return \App\StopWord::findNoindexMatchIn(trim(($raw['name'] ?? '') . ' ' . strip_tags((string) ($raw['content'] ?? '')))) !== null;
    }

    /**
     * Тег із noindex-стоп-словом у назві (обидві мови).
     */
    public static function isSensitiveTag($tag): bool
    {
        if (!$tag) {
            return false;
        }
        $raw = $tag->getAttributes();

        return \App\StopWord::findNoindexMatchIn(trim(($raw['name'] ?? '') . ' ' . ($raw['name_uk'] ?? ''))) !== null;
    }

    /** Профілі бренду для schema.org sameAs */
    public static function sameAs(): array
    {
        return (array) config('seo.same_as', []);
    }
}
