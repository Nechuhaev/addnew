<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ручна позначка тегу (tags:mark-noindex): 1 — noindex і поза sitemap (технічні теги магазинів, загальні слова);
 * 2 — ще й чутливий (без AdSense), як теги зі стоп-словами.
 */
class AddIsNoindexToAdTags extends Migration
{
    public function up()
    {
        Schema::table('ad_tags', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_noindex')->default(0)->index();
        });
    }

    public function down()
    {
        Schema::table('ad_tags', function (Blueprint $table) {
            $table->dropIndex(['is_noindex']);
            $table->dropColumn('is_noindex');
        });
    }
}
