<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Точка історії ціни оголошення (одна на день).
 */
class AdPriceHistory extends Model
{
    protected $table = 'ad_price_history';

    public $timestamps = false;

    protected $guarded = [];

    protected $dates = ['recorded_on'];

    /** Період графіка на сторінці товару, місяців */
    const CHART_MONTHS = 12;

    /**
     * Записати поточну ціну оголошення (викликається з Ad::saved).
     */
    public static function record(Ad $ad): void
    {
        if ((float) $ad->getAttributes()['price'] <= 0) {
            return;
        }
        $currency = AdCurrency::find($ad->currency_id);
        $price = (float) $ad->getAttributes()['price'];

        DB::table('ad_price_history')->updateOrInsert(
            ['ad_id' => $ad->id, 'recorded_on' => Carbon::today()->toDateString()],
            [
                'price' => $price,
                'currency_id' => $ad->currency_id,
                'price_uah' => round($price * ($currency ? ((float) $currency->rate ?: 1) : 1), 2),
            ]
        );
    }

    /**
     * Дані для графіка у валюті оголошення за останні CHART_MONTHS місяців.
     * null — якщо ціна за період не змінювалась (графік не потрібен).
     *
     * @return array|null [points => [[date, value]], min, max, current, from, to, is_lowest_90]
     */
    public static function chartFor(Ad $ad): ?array
    {
        $price = (float) $ad->getAttributes()['price'];
        if ($price <= 0) {
            return null;
        }

        $currency = AdCurrency::find($ad->currency_id);
        $rate = $currency ? ((float) $currency->rate ?: 1) : 1;
        $from = Carbon::today()->subMonths(self::CHART_MONTHS);
        $today = Carbon::today();

        // Остання точка ДО початку періоду — ціна на першу дату графіка
        $before = static::where('ad_id', $ad->id)->where('recorded_on', '<', $from->toDateString())
            ->orderByDesc('recorded_on')->first();
        $rows = static::where('ad_id', $ad->id)->where('recorded_on', '>=', $from->toDateString())
            ->orderBy('recorded_on')->get();

        $value = function (AdPriceHistory $row) use ($ad, $rate) {
            return (int) $row->currency_id === (int) $ad->currency_id
                ? (float) $row->price
                : round((float) $row->price_uah / $rate, 2);
        };

        $points = [];
        if ($before) {
            $points[] = [$from->toDateString(), $value($before)];
        }
        foreach ($rows as $row) {
            $v = $value($row);
            // Однакові сусідні точки не потрібні для ступінчастої лінії
            if ($points && end($points)[1] == $v) {
                continue;
            }
            $points[] = [$row->recorded_on->toDateString(), $v];
        }
        if (!$points || end($points)[1] != $price) {
            $points[] = [$today->toDateString(), $price];
        }

        $values = array_column($points, 1);
        if (count(array_unique($values)) < 2) {
            return null;
        }

        // Мінімум за 90 днів: ціни, що діяли хоча б день у цьому вікні
        $since90 = $today->copy()->subDays(90)->toDateString();
        $window = [];
        foreach ($points as $i => $p) {
            $next = $points[$i + 1][0] ?? null;
            if ($next === null || $next > $since90) {
                $window[] = $p[1];
            }
        }

        return [
            'points' => $points,
            'min' => min($values),
            'max' => max($values),
            'current' => $price,
            'from' => $points[0][0],
            'to' => $today->toDateString(),
            'is_lowest_90' => $price <= min($window) && max($window) > $price,
            'symbol' => $currency->symbol ?? 'грн',
        ];
    }
}
