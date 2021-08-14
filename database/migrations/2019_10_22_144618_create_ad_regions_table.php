<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdRegionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ad_regions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('country_id');
            $table->foreign('country_id')->references('id')->on('ad_countries');
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->text('content')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description', 255)->nullable();
            $table->integer('sort_order')->default(0)->nullable();
            $table->timestamps();

            $table->index('country_id')->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ad_regions');
    }
}
