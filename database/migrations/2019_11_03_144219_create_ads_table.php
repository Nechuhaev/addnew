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
            $table->unsignedInteger('category_id');
            $table->foreign('category_id')->references('id')->on('ad_categories');
            $table->unsignedInteger('city_id');
            $table->foreign('city_id')->references('id')->on('ad_cities');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedSmallInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('ad_currencies');
            $table->string('image');
            $table->string('images')->nullable();
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->text('content');
            $table->integer('price')->nullable();
            $table->string('telephone');
            $table->string('email');
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description', 255)->nullable();
            $table->boolean('status')->default(1);
            $table->integer('total_views')->default(0);
            $table->integer('today_views')->default(0);
            $table->integer('bad_rating')->default(0);
            $table->timestamp('date_active')->useCurrent();
            $table->timestamps();

            $table->index(['city_id', 'id'])->unique();
            $table->index(['category_id', 'id'])->unique();

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
