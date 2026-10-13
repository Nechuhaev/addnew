<?php

use App\Console\Commands\PublishArticle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Конвеєр блогу під пошук і AI-відповіді:
 * 1) у збережений промпт article_generate дописується блок PublishArticle::AI_FORMAT_RULES
 *    («Коротко», H2-питання, чек-листи/таблиці, FAQ, посилання на розділи). Якщо промпт
 *    в адмінці не редагували — він просто оновлюється до нового тексту з коду.
 * 2) у контент-план додаються 15 тем-гайдів з найвищим пріоритетом (рубрика «Гайди»).
 */
class AiGuidesContentPlan extends Migration
{
    const CLUSTER = 'ai-guides';
    const MARKER = 'ФОРМАТ ДЛЯ ПОШУКУ Й AI-ВІДПОВІДЕЙ';

    public function up()
    {
        $row = DB::table('prompt_templates')->where('key', 'article_generate')->first();
        if ($row && strpos((string) $row->template, self::MARKER) === false) {
            $anchor = 'ВНУТРІШНЯ ПЕРЕЛІНКОВКА:';
            $insert = PublishArticle::AI_FORMAT_RULES . "\n";
            $update = function ($text) use ($anchor, $insert) {
                return strpos($text, $anchor) !== false
                    ? str_replace($anchor, $insert . $anchor, $text)
                    : rtrim($text) . "\n\n" . $insert;
            };
            DB::table('prompt_templates')->where('id', $row->id)->update([
                'template' => $update((string) $row->template),
                'default_template' => $update((string) $row->default_template),
                'updated_at' => now(),
            ]);
        }
        // Якщо запису ще немає — його створить сама команда з новим текстом із коду.

        $categoryId = DB::table('article_categories')->where('slug', 'gaidy')->value('id');
        foreach (self::topics() as [$topic, $keyword]) {
            if (DB::table('content_plan_items')->where('topic', $topic)->exists()) {
                continue;
            }
            DB::table('content_plan_items')->insert([
                'cluster' => self::CLUSTER,
                'cluster_volume' => 0,
                'topic' => $topic,
                'focus_keyword_hint' => $keyword,
                'category_id' => $categoryId,
                'priority' => 0,
                'status' => 'planned',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('content_plan_items')->where('cluster', self::CLUSTER)->where('status', 'planned')->delete();

        $row = DB::table('prompt_templates')->where('key', 'article_generate')->first();
        if ($row) {
            $insert = PublishArticle::AI_FORMAT_RULES . "\n";
            DB::table('prompt_templates')->where('id', $row->id)->update([
                'template' => str_replace($insert, '', (string) $row->template),
                'default_template' => str_replace($insert, '', (string) $row->default_template),
            ]);
        }
    }

    protected static function topics(): array
    {
        return [
            ['Оренда квартири: що перевірити до підписання договору', 'оренда квартири договір'],
            ['Як перевірити вживаний автомобіль перед покупкою: чек-лист', 'перевірка авто перед покупкою'],
            ['Як вибрати вживаний ноутбук і що перевірити до оплати', 'вживаний ноутбук як вибрати'],
            ['Як безпечно купити товар з доставкою за оголошенням', 'безпечна покупка з доставкою'],
            ['Як вибрати цуценя: вік, документи, щеплення і характер', 'як вибрати цуценя'],
            ['Вживаний велосипед: як вибрати й перевірити перед покупкою', 'вживаний велосипед'],
            ['Як продати автомобіль самостійно: документи, ціна і безпека', 'продати авто самостійно'],
            ['Як перевірити вживану пральну машину чи холодильник перед покупкою', 'вживана побутова техніка перевірка'],
            ['Як знайти надійного майстра з ремонту за оголошенням', 'майстер з ремонту як вибрати'],
            ['Як скласти оголошення про вакансію, на яке відгукуються', 'оголошення про вакансію'],
            ['Дитяча коляска б/в: на що звернути увагу при покупці', 'коляска б/в'],
            ['Як визначити ціну вживаної речі для продажу', 'ціна вживаної речі'],
            ['Як здати квартиру в оренду безпечно: договір, застава, перевірка орендаря', 'здати квартиру в оренду'],
            ['Як купити вживані меблі й не пошкодувати', 'вживані меблі'],
            ['Як інтернет-магазину підключити товари до дошки оголошень через фід YML або CSV', 'фід товарів YML'],
        ];
    }
}
