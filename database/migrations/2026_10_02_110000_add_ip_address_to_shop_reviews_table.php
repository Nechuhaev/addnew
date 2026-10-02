<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIpAddressToShopReviewsTable extends Migration
{
    /**
     * IP-адреса того, хто залишив відгук — для виявлення накручування
     * (кілька відгуків з однієї IP на різні магазини, чи всі "5 зірок"
     * з однієї адреси тощо).
     */
    public function up()
    {
        Schema::table('shop_reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('shop_reviews', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('comment');
            }
        });
    }

    public function down()
    {
        Schema::table('shop_reviews', function (Blueprint $table) {
            if (Schema::hasColumn('shop_reviews', 'ip_address')) {
                $table->dropColumn('ip_address');
            }
        });
    }
}
