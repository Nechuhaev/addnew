<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToAdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->unsignedBigInteger('source_id')->after('email');
            $table->string('brand', 255)->nullable()->after('source_id');
            $table->string('code')->nullable()->after('brand');
            $table->string('stock', 100)->nullable()->after('code');
            $table->string('condition', 100)->nullable()->after('stock');
            $table->string('url', 255)->nullable()->after('condition');
            $table->boolean('is_product')->default(false)->after('url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn('source_id');
            $table->dropColumn('brand');
            $table->dropColumn('code');
            $table->dropColumn('stock');
            $table->dropColumn('condition');
            $table->dropColumn('url');
            $table->dropColumn('is_product');
        });
    }
}
