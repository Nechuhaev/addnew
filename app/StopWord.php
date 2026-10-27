<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Стоп-слова (адмінка → «Стоп-слова»):
 *  - block   — оголошення з таким словом не публікується;
 *  - noindex — публікується, але його сторінка й теги з таким словом закриті від індексації,
 *              не йдуть у sitemap і показуються без AdSense (App\Services\SeoRules).
 */
class StopWord extends Model
{
    const ACTION_BLOCK = 'block';
    const ACTION_NOINDEX = 'noindex';

    const ACTIONS = [
        self::ACTION_BLOCK => 'Блокувати публікацію',
        self::ACTION_NOINDEX => 'Прибрати з індексації',
    ];

    protected $fillable = ['word', 'action'];

    protected static function boot()
    {
        parent::boot();

        $forget = function () {
            Cache::forget('stop_words_noindex');
        };
        static::saved($forget);
        static::deleted($forget);
    }

    /**
     * Перевіряє, чи містить текст хоч одне стоп-слово, що БЛОКУЄ публікацію
     * (пошук за підрядком, регістронезалежно — узгоджено з логікою
     * старого масового видалення, що вже була в проєкті).
     *
     * @param string $text
     * @return string|null Знайдене стоп-слово, або null якщо чисто
     */
    public static function findMatchIn(string $text): ?string
    {
        return self::matchIn($text, static::where(function ($q) {
            $q->where('action', self::ACTION_BLOCK)->orWhereNull('action');
        })->pluck('word')->all());
    }

    /**
     * Слова, що роблять сторінку чутливою (noindex, без AdSense): «прибрати з індексації»
     * і «блокувати» — заблоковане слово тим більше не має індексуватись, якщо таке
     * оголошення вже є на сайті (кеш 1 год; скидається при зміні списку).
     */
    public static function noindexWords(): array
    {
        return Cache::remember('stop_words_noindex', 3600, function () {
            return static::where(function ($q) {
                $q->whereIn('action', [self::ACTION_NOINDEX, self::ACTION_BLOCK])->orWhereNull('action');
            })->pluck('word')->all();
        });
    }

    /**
     * @return string|null перше знайдене noindex-слово
     */
    public static function findNoindexMatchIn(?string $text): ?string
    {
        return $text === null || $text === '' ? null : self::matchIn($text, self::noindexWords());
    }

    protected static function matchIn(string $text, array $words): ?string
    {
        $textLower = mb_strtolower($text);
        foreach ($words as $word) {
            $word = trim($word);
            if (Str::length($word) < 3) {
                continue;
            }
            if (mb_strpos($textLower, mb_strtolower($word)) !== false) {
                return $word;
            }
        }

        return null;
    }
}
