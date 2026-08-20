<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Translation extends Model
{
    protected $fillable = ['group', 'key', 'locale', 'value'];

    const CACHE_TTL = 3600; // 1 година

    protected static function booted()
    {
        static::saved(function ($translation) {
            self::clearCache($translation->group, $translation->locale);
        });
        static::deleted(function ($translation) {
            self::clearCache($translation->group, $translation->locale);
        });
    }

    public static function clearCache(string $group, string $locale): void
    {
        Cache::forget("translations:{$locale}:{$group}");
    }

    /**
     * Усі переклади групи для конкретної локалі у форматі [key => value],
     * закешовано на годину.
     */
    public static function getGroup(string $group, string $locale): array
    {
        return Cache::remember("translations:{$locale}:{$group}", self::CACHE_TTL, function () use ($group, $locale) {
            return self::where('group', $group)
                ->where('locale', $locale)
                ->whereNotNull('value')
                ->where('value', '!=', '')
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    /**
     * Правильна слов'янська форма множини за числом (0=one, 1=few, 2=many).
     * Laravel'івський trans_choice() у цій версії некоректно обробляє
     * числа з двома+ цифрами (21, 102 тощо) для uk/ru — тому рахуємо самі.
     *
     * Приклади: 1,21,101 -> 0 (one); 2,3,4,22,102 -> 1 (few);
     * 5,11,12,13,14,25,111 -> 2 (many).
     */
    public static function slavicPluralIndex(int $number): int
    {
        $mod10 = $number % 10;
        $mod100 = $number % 100;

        if ($mod10 === 1 && $mod100 !== 11) {
            return 0;
        }
        if ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 10 || $mod100 >= 20)) {
            return 1;
        }
        return 2;
    }

    /**
     * Зручна обгортка: бере три ключі (напр. "offers_count_one",
     * "_few", "_many") і повертає переклад у правильній формі з
     * підставленим :count.
     */
    public static function pluralChoice(string $group, string $baseKey, int $number): string
    {
        $suffixes = ['one', 'few', 'many'];
        $suffix = $suffixes[self::slavicPluralIndex($number)];
        $key = "{$group}.{$baseKey}_{$suffix}";

        return __($key, ['count' => $number]);
    }
}