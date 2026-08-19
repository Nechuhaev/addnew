<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StopWord extends Model
{
    protected $fillable = ['word'];

    /**
     * Перевіряє, чи містить текст хоч одне зі стоп-слів
     * (пошук за підрядком, регістронезалежно — узгоджено з логікою
     * старого масового видалення, що вже була в проєкті).
     *
     * @param string $text
     * @return string|null Знайдене стоп-слово, або null якщо чисто
     */
    public static function findMatchIn(string $text): ?string
    {
        $words = static::pluck('word');
        $textLower = mb_strtolower($text);

        foreach ($words as $word) {
            if (Str::length(trim($word)) < 3) {
                continue;
            }
            if (mb_strpos($textLower, mb_strtolower(trim($word))) !== false) {
                return $word;
            }
        }

        return null;
    }
}