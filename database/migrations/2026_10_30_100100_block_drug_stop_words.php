<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Наркотики заборонені «Правилами розміщення», але стоп-слів для них не було
 * (у Search Console: «jwh-018 купить в украине» на сторінці «Туристичні товари»).
 * Збіг — підрядок (StopWord::matchIn), тож лише слова без легальних значень:
 * без «героин» (= «героиня»), «спайс», «соль», «закладка», «каннабис» (CBD-товари).
 * Наявні оголошення з цими словами — noindex (block входить у noindexWords()),
 * з публікації їх знімає модерація.
 */
class BlockDrugStopWords extends Migration
{
    const WORDS = [
        'jwh-', 'jwh0', 'мефедрон', 'mephedrone', 'амфетамин', 'амфетамін', 'amphetamine',
        'a-pvp', 'альфа-пвп', 'alpha-pvp', 'кокаин', 'кокаїн', 'cocaine', 'экстази', 'екстазі', 'mdma', 'мдма',
    ];

    public function up()
    {
        $existing = DB::table('stop_words')->pluck('word')->map(function ($w) {
            return mb_strtolower(trim($w));
        })->all();
        foreach (self::WORDS as $word) {
            if (in_array($word, $existing, true)) {
                DB::table('stop_words')->whereRaw('LOWER(TRIM(word)) = ?', [$word])->update(['action' => 'block', 'updated_at' => now()]);
            } else {
                DB::table('stop_words')->insert(['word' => $word, 'action' => 'block', 'created_at' => now(), 'updated_at' => now()]);
            }
        }
        \Illuminate\Support\Facades\Cache::forget('stop_words_noindex');
    }

    public function down()
    {
        DB::table('stop_words')->whereIn('word', self::WORDS)->delete();
        \Illuminate\Support\Facades\Cache::forget('stop_words_noindex');
    }
}
