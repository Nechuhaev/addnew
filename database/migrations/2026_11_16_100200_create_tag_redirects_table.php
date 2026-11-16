<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** 301-переадресації зі slug об'єднаних тегів-дублікатів на основний тег (tags:merge-duplicates) */
class CreateTagRedirectsTable extends Migration
{
    public function up()
    {
        Schema::create('tag_redirects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('from_slug', 255)->unique();
            $table->unsignedBigInteger('to_tag_id')->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tag_redirects');
    }
}
