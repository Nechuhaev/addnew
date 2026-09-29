<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyReportsTable extends Migration
{
    /**
     * Один рядок на дату. Кожен *_count/*_summary заповнюється тією
     * командою, до якої відноситься (content:build-plan, content:publish,
     * content:refresh, content:check-index, products:seo-optimize,
     * ads:seo-optimize, tags:seo-optimize) — одразу після свого
     * запуску, через трейт Concerns\UpdatesDailyReport. У дні, коли
     * якась команда не запускалась (більшість — раз на тиждень),
     * відповідні поля лишаються NULL — так в адмінці одразу видно,
     * коли реально був запуск, а коли просто нема даних.
     *
     * ads_count/new_users_count/new_shops_count — виняток, рахуються
     * окремою командою report:daily щоночі за вчорашню добу (не
     * залежать від інших команд).
     */
    public function up()
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');

            // 1-3: оголошення / користувачі / магазини (report:daily)
            $table->unsignedInteger('ads_count')->default(0);
            $table->unsignedInteger('new_users_count')->default(0);
            $table->unsignedInteger('new_shops_count')->default(0);

            // 4: семантика й контент-план (content:build-plan)
            $table->unsignedInteger('semantics_added_count')->nullable();
            $table->unsignedInteger('topics_added_count')->nullable();
            $table->text('clusters_summary')->nullable();

            // 5: опубліковані статті (content:publish)
            $table->unsignedInteger('articles_published_count')->nullable();
            $table->text('articles_published_summary')->nullable();

            // 6: оновлені старі статті (content:refresh)
            $table->unsignedInteger('articles_refreshed_count')->nullable();
            $table->text('articles_refreshed_summary')->nullable();

            // 7: перевірка індексації Google (content:check-index)
            $table->unsignedInteger('indexing_checked_count')->nullable();
            $table->text('indexing_summary')->nullable();

            // 8-9: SEO товарів і звичайних оголошень
            $table->unsignedInteger('products_seo_optimized_count')->nullable();
            $table->unsignedInteger('ads_seo_optimized_count')->nullable();

            // 10: SEO тегів (tags:seo-optimize)
            $table->unsignedInteger('tags_seo_optimized_count')->nullable();
            $table->text('tags_seo_optimized_summary')->nullable();

            $table->timestamps();

            $table->unique('date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('daily_reports');
    }
}
