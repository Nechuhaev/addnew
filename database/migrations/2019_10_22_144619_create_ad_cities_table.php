<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ad_cities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('region_id');
            $table->foreign('region_id')->references('id')->on('ad_regions');
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->text('content')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description', 255)->nullable();
            $table->integer('sort_order')->default(0)->nullable();
            $table->timestamps();

            $table->index('region_id')->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ad_cities');
    }
}
