<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Щоденна статистика Bing Webmaster (seo:bing-stats): покази, кліки, сторінок в індексі, сканування */
class CreateBingDailyStatsTable extends Migration
{
    public function up()
    {
        Schema::create('bing_daily_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date')->unique();
            $table->unsignedInteger('clicks')->nullable();
            $table->unsignedInteger('impressions')->nullable();
            $table->unsignedInteger('in_index')->nullable();
            $table->unsignedInteger('crawled_pages')->nullable();
            $table->unsignedInteger('crawl_errors')->nullable();
            $table->timestamps();
        });

        Schema::table('daily_reports', function (Blueprint $table) {
            $table->text('bing_summary')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bing_daily_stats');
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropColumn('bing_summary');
        });
    }
}
