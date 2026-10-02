<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversationsTable extends Migration
{
    /**
     * Один діалог між магазином (shop_user_id) і покупцем (buyer_user_id),
     * опційно прив'язаний до конкретного оголошення (ad_id) — звідки
     * саме покупець написав. last_message_at — для сортування списку
     * діалогів за свіжістю без JOIN на messages щоразу.
     */
    public function up()
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('shop_user_id');
            $table->unsignedInteger('buyer_user_id');
            $table->unsignedInteger('ad_id')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['shop_user_id', 'last_message_at']);
            $table->index(['buyer_user_id', 'last_message_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('conversations');
    }
}
