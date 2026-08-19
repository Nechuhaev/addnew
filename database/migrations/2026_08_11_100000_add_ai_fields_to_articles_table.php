<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAiFieldsToArticlesTable extends Migration
{
    /**
     * Додає поля, потрібні AI-контент-конвеєру, до вже існуючої таблиці articles.
     * Нічого не видаляє і не змінює в наявних колонках — тільки додає нові,
     * усі nullable/зі значенням за замовчуванням, тому існуючі статті
     * і фронтенд сайту ніяк не постраждають.
     */
    public function up()
    {
        Schema::table('articles', function (Blueprint $table) {
            if (!Schema::hasColumn('articles', 'focus_keyword')) {
                $table->string('focus_keyword')->nullable()->after('meta_description');
            }
            if (!Schema::hasColumn('articles', 'tags')) {
                $table->text('tags')->nullable()->after('focus_keyword');
            }
            if (!Schema::hasColumn('articles', 'ai_generated')) {
                $table->boolean('ai_generated')->default(false)->after('tags');
            }
        });
    }

    public function down()
    {
        Schema::table('articles', function (Blueprint $table) {
            if (Schema::hasColumn('articles', 'ai_generated')) {
                $table->dropColumn('ai_generated');
            }
            if (Schema::hasColumn('articles', 'tags')) {
                $table->dropColumn('tags');
            }
            if (Schema::hasColumn('articles', 'focus_keyword')) {
                $table->dropColumn('focus_keyword');
            }
        });
    }
}
