<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromptTemplatesTable extends Migration
{
    /**
     * Зберігає ВСІ промпти, що використовуються AI-командами сайту,
     * для редагування через адмінку "Промпти". Кожен запис створюється
     * автоматично при першому запуску відповідної команди (через
     * firstOrCreate у трейті Concerns\UsesPromptTemplates) — нічого
     * вручну заповнювати не треба.
     *
     * default_template — оригінальний (початковий) текст промпту з коду,
     * ніколи не змінюється адміном; потрібен для кнопки "Скинути до
     * замовчування". template — поточний, можливо відредагований варіант,
     * саме він реально використовується.
     */
    public function up()
    {
        Schema::create('prompt_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key', 191);
            $table->string('command_class', 255)->nullable();
            $table->string('label', 255);
            $table->text('description')->nullable();
            $table->longText('default_template');
            $table->longText('template');
            $table->timestamps();

            $table->unique('key');
        });
    }

    public function down()
    {
        Schema::dropIfExists('prompt_templates');
    }
}
