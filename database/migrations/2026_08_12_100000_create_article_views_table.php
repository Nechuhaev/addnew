<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleViewsTable extends Migration
{
    public function up()
    {
        Schema::create('article_views', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Без foreign key: таблиця articles має ENGINE=MyISAM,
            // MyISAM не підтримує foreign key constraints взагалі.
            // Цілісність (напр. чистка при видаленні статті) — на рівні коду.
            $table->unsignedBigInteger('article_id');
            // 'view' зараз; у майбутньому можна додати 'contact_click',
            // 'phone_reveal' тощо для конверсій без зміни структури.
            $table->string('event_type', 50)->default('view');
            // SHA-256 хеш IP, не сирий IP — для дедуплікації без зберігання PII.
            $table->string('ip_hash', 64)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['article_id', 'created_at']);
            $table->index(['article_id', 'event_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('article_views');
    }
}