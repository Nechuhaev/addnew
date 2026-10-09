<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Товар/оголошення в обраному користувача.
 */
class Favorite extends Model
{
    protected $guarded = [];

    protected $casts = [
        'notify' => 'boolean',
        'notified_at' => 'datetime',
    ];

    /** Мінімальне зниження ціни (частка), від якого надсилаємо лист */
    const DROP_THRESHOLD = 0.03;

    /** @var array<int, array<int, true>> ID обраних оголошень по користувачах (кеш на запит) */
    protected static $idsCache = [];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Множина ID оголошень в обраному користувача: [ad_id => true].
     */
    public static function idsFor(?int $userId): array
    {
        if (!$userId) {
            return [];
        }
        if (!isset(static::$idsCache[$userId])) {
            static::$idsCache[$userId] = array_fill_keys(
                static::where('user_id', $userId)->pluck('ad_id')->all(),
                true
            );
        }
        return static::$idsCache[$userId];
    }

    public static function forgetIds(int $userId): void
    {
        unset(static::$idsCache[$userId]);
    }

    /**
     * Поточна ціна оголошення в гривнях (price * курс валюти).
     */
    public static function priceUah(Ad $ad): float
    {
        $rate = $ad->currency ? (float) $ad->currency->rate : 1.0;
        return round((float) $ad->price * ($rate ?: 1.0), 2);
    }

    /**
     * Токен для відписки зі листа (без входу на сайт).
     */
    public static function unsubscribeToken(int $userId): string
    {
        return substr(hash_hmac('sha256', 'price-drop-unsubscribe|' . $userId, config('app.key')), 0, 32);
    }

    public static function unsubscribeUrl(int $userId): string
    {
        return route('favorites.unsubscribe', ['user' => $userId, 'token' => static::unsubscribeToken($userId)]);
    }

    /**
     * Зниження ціни: повертає відсоток зниження відносно бази або null.
     */
    public function dropPercent(float $currentUah): ?float
    {
        $base = (float) $this->base_price_uah;
        if ($base <= 0 || $currentUah <= 0 || $currentUah > $base * (1 - self::DROP_THRESHOLD)) {
            return null;
        }
        return round(($base - $currentUah) / $base * 100, 1);
    }
}
