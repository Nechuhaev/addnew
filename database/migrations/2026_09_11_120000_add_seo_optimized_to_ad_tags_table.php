<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSeoOptimizedToAdTagsTable extends Migration
{
    /**
     * Той самий підхід, що вже використовується для товарів/оголошень
     * (Ad::seo_optimized) — розрізняє теги, що вже отримали УНІКАЛЬНИЙ
     * SEO-текст від LLM, від тих, де досі старий шаблонний текст із
     * простою підстановкою назви. Усі наявні теги за замовчуванням
     * seo_optimized=false — щоденний пакетний прогін tags:seo-optimize
     * сам поступово пройдеться по всіх існуючих, по 10 за раз.
     */
    public function up()
    {
        Schema::table('ad_tags', function (Blueprint $table) {
            if (!Schema::hasColumn('ad_tags', 'seo_optimized')) {
                $table->boolean('seo_optimized')->default(false);
            }
            if (!Schema::hasColumn('ad_tags', 'seo_optimized_at')) {
                $table->timestamp('seo_optimized_at')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('ad_tags', function (Blueprint $table) {
            if (Schema::hasColumn('ad_tags', 'seo_optimized_at')) {
                $table->dropColumn('seo_optimized_at');
            }
            if (Schema::hasColumn('ad_tags', 'seo_optimized')) {
                $table->dropColumn('seo_optimized');
            }
        });
    }
}
