<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * ГБЛ (гамма-бутиролактон, оксолан-2-он) — прекурсор психотропних речовин; у Search Console (листопад 2026)
 * оголошення «gamma-butirolakton-oksolan-2-on-gbl» отримували кліки. Збіг — підрядок (StopWord::matchIn),
 * тож коротких «гбл»/«gbl» окремо немає (зачепили б інші слова), а «оксолан» — лише як «оксолан-2(-он)»: просто «оксолан» — це ще й тетрагідрофуран, звичайний розчинник.
 * Наявні оголошення знімає ads:moderate (правило «наркотики»), нові не публікуються.
 * Наявні оголошення — noindex (block входить у noindexWords()), з публікації їх знімає модерація.
 */
class BlockGblStopWords extends Migration
{
    const WORDS = [
        'бутиролактон', 'butyrolactone', 'butirolakton', 'оксолан-2', 'oxolan-2', 'oksolan-2',
        'гбл купить', 'гбл купити', 'gbl купить', 'gbl купити', 'купить gbl', 'купить гбл', 'купити гбл', 'купити gbl',
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
