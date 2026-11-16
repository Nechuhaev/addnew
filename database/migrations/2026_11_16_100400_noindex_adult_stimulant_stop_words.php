<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Збуджувальні засоби («шпанская мушка», «возбуждающие капли») продаються в розділі «Предлагаю работу»
 * й поруч з ескорт-тегами (аналіз топ-тегів, 16.11.2026). Це не заборонений товар, тому action = noindex:
 * оголошення стають чутливими (noindex, без AdSense, поза списками), але не знімаються з публікації.
 */
class NoindexAdultStimulantStopWords extends Migration
{
    const WORDS = [
        'возбуждающ', 'збуджуюч', 'шпанская мушка', 'шпанська мушка', 'возбудитель для', 'збудник для',
    ];

    public function up()
    {
        $existing = DB::table('stop_words')->pluck('word')->map(function ($w) {
            return mb_strtolower(trim($w));
        })->all();
        foreach (self::WORDS as $word) {
            if (in_array($word, $existing, true)) {
                DB::table('stop_words')->whereRaw('LOWER(TRIM(word)) = ?', [$word])->update(['action' => 'noindex', 'updated_at' => now()]);
            } else {
                DB::table('stop_words')->insert(['word' => $word, 'action' => 'noindex', 'created_at' => now(), 'updated_at' => now()]);
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
