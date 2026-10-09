<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Усі email, знайдені на сайті кандидата (найкращий — у email, решта — підказкою)
 */
class AddEmailCandidatesToShopLeadsTable extends Migration
{
    public function up()
    {
        Schema::table('shop_leads', function (Blueprint $table) {
            $table->text('email_candidates')->nullable()->after('email_lookup_status');
        });
    }

    public function down()
    {
        Schema::table('shop_leads', function (Blueprint $table) {
            $table->dropColumn('email_candidates');
        });
    }
}
