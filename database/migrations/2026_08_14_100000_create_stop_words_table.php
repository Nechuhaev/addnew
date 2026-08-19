<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStopWordsTable extends Migration
{
    public function up()
    {
        Schema::create('stop_words', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('word', 191);
            $table->timestamps();

            $table->unique('word');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stop_words');
    }
}