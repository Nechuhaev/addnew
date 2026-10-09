<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Штрихкод товару (GTIN/EAN) з фідів — однаковий у всіх магазинів,
 * за ним шукаємо той самий товар в інших продавців.
 */
class AddGtinToAdsTable extends Migration
{
    public function up()
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->string('gtin', 20)->nullable()->after('code');
            $table->index('gtin');
        });

        Schema::table('temp_products', function (Blueprint $table) {
            $table->string('gtin', 20)->nullable();
        });
    }

    public function down()
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropIndex(['gtin']);
            $table->dropColumn('gtin');
        });

        Schema::table('temp_products', function (Blueprint $table) {
            $table->dropColumn('gtin');
        });
    }
}
