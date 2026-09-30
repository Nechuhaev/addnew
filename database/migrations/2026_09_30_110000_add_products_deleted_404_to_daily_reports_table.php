<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductsDeleted404ToDailyReportsTable extends Migration
{
    /**
     * Скільки товарів автоматично видалено через 404 від джерела
     * (products:monitor-prices) — джерело точно підтвердило, що
     * сторінки товару вже не існує, на відміну від тимчасового збою
     * чи захисту від ботів.
     */
    public function up()
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_reports', 'products_deleted_404_count')) {
                $table->unsignedInteger('products_deleted_404_count')->nullable()->after('ads_seo_optimized_count');
            }
        });
    }

    public function down()
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            if (Schema::hasColumn('daily_reports', 'products_deleted_404_count')) {
                $table->dropColumn('products_deleted_404_count');
            }
        });
    }
}
