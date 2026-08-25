<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUkFieldsToPagesTable extends Migration
{
    public function up()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('name_uk', 255)->nullable()->after('name');
            $table->longText('content_uk')->nullable()->after('content');
            $table->string('meta_title_uk', 255)->nullable()->after('meta_title');
            $table->string('meta_description_uk', 255)->nullable()->after('meta_description');
        });
    }

    public function down()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['name_uk', 'content_uk', 'meta_title_uk', 'meta_description_uk']);
        });
    }
}