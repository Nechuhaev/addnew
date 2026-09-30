<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToShopEmailChecksTable extends Migration
{
    /**
     * Розрізняє "сайт узагалі не відповідає" (domain_unreachable) від
     * "сайт живий, але email на ньому не знайдено" (not_found) — раніше
     * обидва випадки виглядали однаково в логах.
     */
    public function up()
    {
        Schema::table('shop_email_checks', function (Blueprint $table) {
            if (!Schema::hasColumn('shop_email_checks', 'status')) {
                $table->string('status', 30)->nullable()->after('matched');
            }
        });
    }

    public function down()
    {
        Schema::table('shop_email_checks', function (Blueprint $table) {
            if (Schema::hasColumn('shop_email_checks', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
}
