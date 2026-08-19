<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompetitorPriceMonitoringTables extends Migration
{
    public function up()
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->string('competitor_url', 500)->nullable()->after('url');
        });

        Schema::create('product_price_checks', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Без foreign key — ads має ENGINE=MyISAM, FK не підтримується.
            $table->unsignedBigInteger('ad_id');
            $table->decimal('old_price', 12, 2)->nullable();
            $table->decimal('found_price', 12, 2)->nullable();
            $table->string('found_currency', 10)->nullable();
            $table->boolean('price_applied')->default(false);
            // success / not_found / currency_mismatch / fetch_error
            $table->string('status', 30);
            $table->text('note')->nullable();
            $table->timestamp('checked_at');

            $table->index(['ad_id', 'checked_at']);
        });
    }

    public function down()
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn('competitor_url');
        });
        Schema::dropIfExists('product_price_checks');
    }
}