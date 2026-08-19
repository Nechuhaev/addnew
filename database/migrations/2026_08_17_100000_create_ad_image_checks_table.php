<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdImageChecksTable extends Migration
{
    public function up()
    {
        Schema::create('ad_image_checks', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Без foreign key — ads має ENGINE=MyISAM, FK не підтримується.
            $table->unsignedBigInteger('ad_id');
            $table->string('status', 20); // ok / broken / error
            $table->timestamp('checked_at');

            $table->index(['ad_id', 'checked_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ad_image_checks');
    }
}