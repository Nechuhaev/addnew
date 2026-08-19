<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSeoOptimizedToAdsTable extends Migration
{
    public function up()
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->boolean('seo_optimized')->default(false)->after('competitor_url');
            $table->timestamp('seo_optimized_at')->nullable()->after('seo_optimized');
        });
    }

    public function down()
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn(['seo_optimized', 'seo_optimized_at']);
        });
    }
}