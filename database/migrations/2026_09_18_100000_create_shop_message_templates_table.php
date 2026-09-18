<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopMessageTemplatesTable extends Migration
{
    /**
     * Готові шаблони листів для розсилки магазинам (адмінка "Написати
     * магазину"). subject/body можуть містити плейсхолдери {{shop_name}},
     * {{shop_id}}, {{shop_url}}, {{password_reset_url}} — підставляються
     * JS-ом на сторінці редагування магазину при виборі шаблону зі списку.
     */
    public function up()
    {
        Schema::create('shop_message_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->string('subject', 255);
            $table->longText('body');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shop_message_templates');
    }
}
