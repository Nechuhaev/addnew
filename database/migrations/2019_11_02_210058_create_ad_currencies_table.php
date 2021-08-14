<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ad_currencies', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('name', 50);
            $table->float('rate');
            $table->string('code', 3);
            $table->string('symbol', 10);
            $table->string('is_default', 1)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ad_currencies');
    }
}
