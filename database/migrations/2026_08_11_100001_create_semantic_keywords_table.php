<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSemanticKeywordsTable extends Migration
{
    /**
     * Накопичений пул пошукових ключових фраз (семантичне ядро).
     * Наповнюється командою `php artisan content:build-plan` —
     * реальними даними з Serpstat (якщо є токен) або вигаданими Claude.
     */
    public function up()
    {
        Schema::create('semantic_keywords', function (Blueprint $table) {
            $table->increments('id');
            // string(191), а не 500 і без unique() — на старому MySQL/utf8mb4
            // унікальний індекс на довгому тексті може впасти з помилкою
            // "Specified key was too long". Дедублікацію ключів робить сам
            // код команди build-plan (перевірка перед вставкою), а не БД.
            $table->string('keyword', 191);
            $table->unsignedInteger('volume')->nullable();
            $table->decimal('cpc', 8, 2)->nullable();
            $table->string('source', 20)->default('claude'); // 'serpstat' | 'claude'
            $table->timestamps();

            $table->index('keyword');
        });
    }

    public function down()
    {
        Schema::dropIfExists('semantic_keywords');
    }
}
