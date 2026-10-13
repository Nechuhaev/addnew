<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ads.is_sensitive — оголошення з чутливим вмістом (App\Services\SeoRules::isSensitiveAd):
 * не показується в списках інших розділів, пошуку, на головній і серед схожих оголошень.
 * Заповнюється при збереженні оголошення і командою ads:mark-sensitive (щогодини;
 * після міграції запустіть: php artisan ads:mark-sensitive --full).
 */
class AddIsSensitiveToAdsTable extends Migration
{
    public function up()
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->boolean('is_sensitive')->default(0)->after('status');
            $table->index('is_sensitive');
        });
    }

    public function down()
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropIndex(['is_sensitive']);
            $table->dropColumn('is_sensitive');
        });
    }
}
