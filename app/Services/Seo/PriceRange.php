<?php

namespace App\Services\Seo;

use App\Ad;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Діапазон цін для SEO-текстів, стійкий до сміття в даних.
 *
 * У базі трапляються заглушки на кшталт 2147483647 (INT_MAX) або «1 грн»,
 * тому простий min–max для широких тегів/магазинів дає неправдивий текст.
 * Тут: (1) ціни ≥ MAX_SANE відкидаються; (2) якщо цін багато (≥20),
 * береться діапазон 10–90-го перцентиля і текст має казати «більшість цін»;
 * (3) ціни рахуються лише в домінантній валюті.
 */
class PriceRange
{
    /** Ціни від цього порога вважаємо сміттям. */
    const MAX_SANE = 100000000;

    /**
     * @param callable $scope function (Builder $q): void — обмежує оголошення
     *        (по user_id чи через join ad_tag). Таблиця оголошень: 'ads'.
     * @return array|null ['lo' => string, 'hi' => string, 'approx' => bool]
     */
    public static function summarize(callable $scope): ?array
    {
        $cur = static::query($scope)
            ->whereNotNull('ads.currency_id')
            ->select('ads.currency_id', DB::raw('COUNT(*) AS c'))
            ->groupBy('ads.currency_id')
            ->orderByDesc('c')
            ->first();
        if (!$cur) {
            return null;
        }

        $prices = static::query($scope)
            ->where('ads.currency_id', $cur->currency_id)
            ->pluck('ads.price')
            ->map(function ($p) {
                return (float) $p;
            })
            ->sort()
            ->values();

        $n = $prices->count();
        if ($n < 2) {
            return null;
        }

        $approx = $n >= 20;
        if ($approx) {
            $lo = $prices[(int) floor(($n - 1) * 0.10)];
            $hi = $prices[(int) floor(($n - 1) * 0.90)];
        } else {
            $lo = $prices->first();
            $hi = $prices->last();
        }
        if (floor($lo) >= floor($hi)) {
            return null;
        }

        $loLabel = static::labelNear($scope, $cur->currency_id, $lo);
        $hiLabel = static::labelNear($scope, $cur->currency_id, $hi);
        if (!$loLabel || !$hiLabel) {
            return null;
        }

        return ['lo' => $loLabel, 'hi' => $hiLabel, 'approx' => $approx];
    }

    protected static function query(callable $scope): Builder
    {
        $q = Ad::query();
        $scope($q);

        return $q->where('ads.price', '>', 0)->where('ads.price', '<', self::MAX_SANE);
    }

    /**
     * Підпис ціни (з валютою) для оголошення, найближчого до цільового значення.
     */
    protected static function labelNear(callable $scope, $currencyId, float $target): ?string
    {
        $ad = static::query($scope)
            ->where('ads.currency_id', $currencyId)
            ->select('ads.*')
            ->orderByRaw('ABS(ads.price - ?) ASC', [$target])
            ->first();
        if (!$ad) {
            return null;
        }

        try {
            $label = html_entity_decode(strip_tags((string) $ad->formatted_price), ENT_QUOTES, 'UTF-8');
        } catch (\Throwable $e) {
            return null;
        }
        $label = trim(preg_replace('/\s+/u', ' ', str_replace("\xC2\xA0", ' ', $label)));
        $label = rtrim($label, '. ');

        return $label !== '' ? $label : null;
    }
}
