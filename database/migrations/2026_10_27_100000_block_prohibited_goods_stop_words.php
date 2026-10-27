<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Стероїди заборонені «Правилами розміщення», але їхні стоп-слова були лише «прибрати з
 * індексації» — оголошення публікувались (у Search Console: «нандролон купить»). Конкретні
 * назви препаратів переводимо в «блокувати» (сторінки й далі noindex — StopWord::noindexWords()
 * враховує й block). Загальні «стероид»/«анаболич» лишаються noindex, щоб не блокувати,
 * наприклад, «нестероидные» мазі.
 *
 * Кодграббери НЕ блокуються при публікації: «кодграб» є й у легальних оголошеннях
 * («сигналізація із захистом від кодграббера»). Їх знімає щотижнева модерація (ads:moderate,
 * з винятками «захист/защита»), а сторінки закриті від індексації (noindex).
 */
class BlockProhibitedGoodsStopWords extends Migration
{
    const TO_BLOCK = [
        'нандролон', 'станоджект', 'stanoject', 'стромбаджект', 'strombaject', 'оксиметолон', 'метилтестостерон',
    ];

    const ADD_BLOCK = ['тренболон', 'туринабол', 'оксандролон', 'станозолол'];

    public function up()
    {
        DB::table('stop_words')->whereIn('word', self::TO_BLOCK)->update(['action' => 'block', 'updated_at' => now()]);

        $existing = DB::table('stop_words')->pluck('word')->map(function ($w) {
            return mb_strtolower(trim($w));
        })->all();
        foreach (array_merge(self::TO_BLOCK, self::ADD_BLOCK) as $word) {
            if (!in_array($word, $existing, true)) {
                DB::table('stop_words')->insert(['word' => $word, 'action' => 'block', 'created_at' => now(), 'updated_at' => now()]);
            }
        }
        \Illuminate\Support\Facades\Cache::forget('stop_words_noindex');
    }

    public function down()
    {
        DB::table('stop_words')->whereIn('word', self::ADD_BLOCK)->delete();
        DB::table('stop_words')->whereIn('word', self::TO_BLOCK)->update(['action' => 'noindex', 'updated_at' => now()]);
        \Illuminate\Support\Facades\Cache::forget('stop_words_noindex');
    }
}
