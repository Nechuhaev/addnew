<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentPlanItemsTable extends Migration
{
    /**
     * Черга тем: planned → in_progress → published.
     * Наповнюється `php artisan content:build-plan`,
     * розбирається `php artisan content:publish`.
     */
    public function up()
    {
        Schema::create('content_plan_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('cluster', 255)->nullable();
            $table->unsignedInteger('cluster_volume')->default(0);
            $table->string('topic', 500);
            $table->string('focus_keyword_hint', 255)->nullable();
            $table->unsignedInteger('category_id')->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->string('status', 20)->default('planned'); // planned | in_progress | published
            $table->unsignedInteger('article_id')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('priority');
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_plan_items');
    }
}
