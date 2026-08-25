<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUkFieldsToArticlesTable extends Migration
{
    public function up()
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('name_uk', 255)->nullable()->after('name');
            $table->text('excerpt_uk')->nullable()->after('excerpt');
            $table->longText('content_uk')->nullable()->after('content');
            $table->string('meta_title_uk', 255)->nullable()->after('meta_title');
            $table->string('meta_description_uk', 255)->nullable()->after('meta_description');
        });
    }

    public function down()
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['name_uk', 'excerpt_uk', 'content_uk', 'meta_title_uk', 'meta_description_uk']);
        });
    }
}