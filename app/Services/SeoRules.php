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

    /** Профілі бренду для schema.org sameAs */
    public static function sameAs(): array
    {
        return (array) config('seo.same_as', []);
    }
}
