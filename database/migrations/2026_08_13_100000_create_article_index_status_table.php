<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleIndexStatusTable extends Migration
{
    public function up()
    {
        Schema::create('article_index_status', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Без foreign key — articles має ENGINE=MyISAM, FK не підтримується.
            $table->unsignedBigInteger('article_id');
            $table->string('verdict', 30)->nullable();          // PASS / FAIL / NEUTRAL / PARTIAL
            $table->string('coverage_state', 255)->nullable();   // напр. "Submitted and indexed"
            $table->string('indexing_state', 60)->nullable();
            $table->string('robots_txt_state', 60)->nullable();
            $table->timestamp('last_crawl_time')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->text('raw_response')->nullable();
            $table->timestamps();

            $table->unique('article_id'); // одна статті — один актуальний рядок статусу
        });
    }

    public function down()
    {
        Schema::dropIfExists('article_index_status');
    }
}