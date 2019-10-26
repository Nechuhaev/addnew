<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('category_id');
            $table->integer('city_id');
            $table->string('image', 255)->nullable();
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->string('author');
            $table->string('content');
            $table->string('price')->nullable();
            $table->string('telephone');
            $table->string('email');
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description', 255)->nullable();
            $table->boolean('is_archive')->default(0);
            $table->integer('total_views')->default(0);
            $table->integer('today_views')->default(0);
            $table->integer('bad_rating')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ads');
    }
}
