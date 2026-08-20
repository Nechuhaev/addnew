<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranslationsTable extends Migration
{
    public function up()
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('group', 100);   // напр. "front", "blog", "shop"
            $table->string('key', 255);      // напр. "read_more", "add_to_cart"
            $table->string('locale', 5);     // "uk" / "ru"
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['group', 'key', 'locale'], 'translations_unique');
            $table->index(['group', 'locale']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('translations');
    }
}