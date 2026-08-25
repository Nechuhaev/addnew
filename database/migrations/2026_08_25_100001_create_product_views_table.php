<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductViewsTable extends Migration
{
    public function up()
    {
        Schema::create('product_views', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ad_id');
            $table->string('event_type', 30); // view, click_contacts, click_shop_link, time_on_page
            $table->unsignedInteger('duration_seconds')->nullable(); // тільки для time_on_page
            $table->string('ip_hash', 64);
            $table->timestamp('created_at')->useCurrent();

            $table->index('ad_id');
            $table->index(['ad_id', 'event_type']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_views');
    }
}