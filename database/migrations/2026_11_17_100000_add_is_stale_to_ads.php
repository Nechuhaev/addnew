<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ads.is_stale — старе оголошення без показів у Google (ads:stale-audit): лишається на сайті,
 * але noindex і поза sitemap. Знімається автоматично, коли автор редагує чи піднімає оголошення.
 */
class AddIsStaleToAds extends Migration
{
    public function up()
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->boolean('is_stale')->default(0)->index();
        });
    }

    public function down()
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropIndex(['is_stale']);
            $table->dropColumn('is_stale');
        });
    }
}
