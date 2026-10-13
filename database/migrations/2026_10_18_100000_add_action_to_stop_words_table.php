<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Стоп-слова двох типів:
 *  - block   — як і раніше: оголошення з таким словом не публікується;
 *  - noindex — оголошення публікується, але сторінки з ним (оголошення, тег) закриваються
 *              від індексації (noindex, follow), не потрапляють у sitemap і показуються без AdSense.
 * Стартовий список noindex — інтим/ескорт-послуги, стероїди, кодграбери, продаж акаунтів
 * (за даними Search Console). Слово, що вже є в списку як block, не дублюється.
 */
class AddActionToStopWordsTable extends Migration
{
    const NOINDEX_WORDS = [
        // інтим / ескорт
        'эскорт', 'ескорт', 'escort', 'проститу', 'путан', 'индивидуалк', 'індивідуалк',
        'девушки по вызову', 'девочки по вызову', 'дівчата за викликом', 'дівчата по виклику',
        'интим услуг', 'интим-услуг', 'інтим послуг', 'інтим-послуг', 'секс услуг', 'секс-услуг',
        'секс знакомств', 'секс-знакомств', 'интим знакомств', 'інтим знайомств',
        'эротический массаж', 'эротичный массаж', 'еротичний масаж', 'массаж с продолжением',
        // стероїди
        'нандролон', 'станоджект', 'stanoject', 'стромбаджект', 'strombaject', 'оксиметолон',
        'метилтестостерон', 'анаболич', 'анаболіч', 'стероид', 'стероїд',
        // кодграбери
        'кодграб', 'код-граб', 'codegrab', 'code grab',
        // продаж акаунтів
        'аккаунты olx', 'аккаунт olx', 'акаунти olx', 'акаунт olx', 'аккаунты gmail', 'акаунти gmail',
        'аккаунты google', 'акаунти google', 'аккаунт tiktok', 'аккаунты tiktok', 'акаунт tiktok', 'акаунти tiktok',
    ];

    public function up()
    {
        Schema::table('stop_words', function (Blueprint $table) {
            $table->string('action', 10)->default('block')->after('word');
        });

        $existing = DB::table('stop_words')->pluck('word')->map(function ($w) {
            return mb_strtolower(trim($w));
        })->all();
        $now = now();
        foreach (self::NOINDEX_WORDS as $word) {
            if (!in_array(mb_strtolower($word), $existing, true)) {
                DB::table('stop_words')->insert(['word' => $word, 'action' => 'noindex', 'created_at' => $now, 'updated_at' => $now]);
                $existing[] = mb_strtolower($word);
            }
        }
    }

    public function down()
    {
        DB::table('stop_words')->where('action', 'noindex')->whereIn('word', self::NOINDEX_WORDS)->delete();
        Schema::table('stop_words', function (Blueprint $table) {
            $table->dropColumn('action');
        });
    }
}
