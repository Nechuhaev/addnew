<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** IP, з якої зареєстровано акаунт (захист від масових реєстрацій ботів, стовпчик в адмінці) */
class AddRegisterIpToUsers extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('register_ip', 45)->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['register_ip']);
            $table->dropColumn('register_ip');
        });
    }
}
