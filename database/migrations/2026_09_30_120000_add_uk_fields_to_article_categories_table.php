<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUkFieldsToArticleCategoriesTable extends Migration
{
    /**
     * Категорії блогу (ArticleCategory) ніколи не мали мовного механізму
     * (на відміну від AdCategory/AdTag/AdCity тощо) — звідси рубрики на
     * /blog показувались лише російською незалежно від локалі.
     */
    public function up()
    {
        Schema::table('article_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('article_categories', 'name_uk')) {
                $table->string('name_uk')->nullable()->after('name');
            }
            if (!Schema::hasColumn('article_categories', 'content_uk')) {
                $table->text('content_uk')->nullable()->after('content');
            }
            if (!Schema::hasColumn('article_categories', 'meta_title_uk')) {
                $table->string('meta_title_uk')->nullable()->after('meta_title');
            }
            if (!Schema::hasColumn('article_categories', 'meta_description_uk')) {
                $table->string('meta_description_uk')->nullable()->after('meta_description');
            }
        });
    }

    public function down()
    {
        Schema::table('article_categories', function (Blueprint $table) {
            foreach (['name_uk', 'content_uk', 'meta_title_uk', 'meta_description_uk'] as $col) {
                if (Schema::hasColumn('article_categories', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
