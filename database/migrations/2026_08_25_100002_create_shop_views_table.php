<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopViewsTable extends Migration
{
    public function up()
    {
        Schema::create('shop_views', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id'); // власник магазину (автор сторінки /author/{id})
            $table->string('ip_hash', 64);
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shop_views');
    }
}