<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUkFieldsToSeoFieldsTable extends Migration
{
    public function up()
    {
        Schema::table('seo_fields', function (Blueprint $table) {
            $table->string('meta_title_uk', 255)->nullable()->after('meta_title');
            $table->string('meta_description_uk', 255)->nullable()->after('meta_description');
            $table->longText('description_uk')->nullable()->after('description');
        });
    }

    public function down()
    {
        Schema::table('seo_fields', function (Blueprint $table) {
            $table->dropColumn(['meta_title_uk', 'meta_description_uk', 'description_uk']);
        });
    }
}