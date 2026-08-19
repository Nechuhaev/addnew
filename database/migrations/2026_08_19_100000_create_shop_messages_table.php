<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopMessagesTable extends Migration
{
    public function up()
    {
        Schema::create('shop_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('shop_user_id');
            $table->unsignedBigInteger('admin_user_id')->nullable();
            $table->string('subject', 255);
            $table->text('body');
            $table->string('sent_to_email', 255);
            $table->boolean('sent_successfully')->default(true);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('shop_user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shop_messages');
    }
}