<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameUkToAdCategoriesTable extends Migration
{
    public function up()
    {
        Schema::table('ad_categories', function (Blueprint $table) {
            $table->string('name_uk', 255)->nullable()->after('name');
        });
    }

    public function down()
    {
        Schema::table('ad_categories', function (Blueprint $table) {
            $table->dropColumn('name_uk');
        });
    }
}