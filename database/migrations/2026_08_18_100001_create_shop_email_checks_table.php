<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopEmailChecksTable extends Migration
{
    public function up()
    {
        Schema::create('shop_email_checks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('old_email', 255)->nullable();
            $table->string('found_email', 255)->nullable();
            $table->boolean('matched')->default(false);
            $table->timestamp('checked_at');

            $table->index(['user_id', 'checked_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('shop_email_checks');
    }
}