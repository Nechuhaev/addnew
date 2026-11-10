<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Анаболічні стероїди й підроблені документи: у Search Console (жовтень–листопад 2026) сайт показувався
 * за «анаполон купити», «болденон Київ», «сустандрол», «enandrol», «станоджект», «купить паспорт евросоюза».
 * Збіг — підрядок (StopWord::matchIn), тому лише назви препаратів без легальних значень
 * (без «тестостерон» — легальні бустери) і фрази про паспорт ЄС (без «купить паспорт» — обкладинки).
 * Наявні оголошення — noindex (block входить у noindexWords()), з публікації їх знімає модерація.
 */
class BlockSteroidStopWords extends Migration
{
    const WORDS = [
        'анаполон', 'anapolon', 'болденон', 'boldenon', 'сустандрол', 'sustandrol', 'сустанон', 'sustanon',
        'енандрол', 'энандрол', 'enandrol', 'станоджект', 'stanoject', 'стромбаджект', 'strombaject',
        'андролик', 'androlic', 'оксандролон', 'oxandrolon', 'туринабол', 'turinabol', 'тренболон', 'trenbolon',
        'станозолол', 'stanozolol', 'метандиенон', 'метандієнон', 'methandienone', 'винстрол', 'winstrol',
        'паспорт евросоюза', 'паспорт євросоюзу', 'паспорт европейского союза',
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
