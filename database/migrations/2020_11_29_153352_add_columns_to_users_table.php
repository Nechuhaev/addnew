<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_shop_owner')->default(0)->after('is_admin');
            $table->string('instagram_url')->nullable()->after('twitter_url');
            $table->string('telegram_url')->nullable()->after('twitter_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_shop_owner',
                'instagram_url',
                'telegram_url'
            ]);
        });
    }
}
