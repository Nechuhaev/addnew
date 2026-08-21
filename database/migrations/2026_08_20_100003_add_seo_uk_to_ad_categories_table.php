<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSeoUkToAdCategoriesTable extends Migration
{
    public function up()
    {
        Schema::table('ad_categories', function (Blueprint $table) {
            $table->string('meta_title_uk', 255)->nullable()->after('meta_title');
            $table->string('meta_description_uk', 255)->nullable()->after('meta_description');
            $table->longText('content_uk')->nullable()->after('content');
        });
    }

    public function down()
    {
        Schema::table('ad_categories', function (Blueprint $table) {
            $table->dropColumn(['meta_title_uk', 'meta_description_uk', 'content_uk']);
        });
    }
}