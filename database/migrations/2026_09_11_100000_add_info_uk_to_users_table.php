<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInfoUkToUsersTable extends Migration
{
    /**
     * Український переклад опису магазину (поле info на User).
     * TEXT, а не VARCHAR(5000) як оригінал — переклад іноді довший
     * за вихідний текст, тож безпечніше не обмежувати довжину так само.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'info_uk')) {
                $table->text('info_uk')->nullable()->after('info');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'info_uk')) {
                $table->dropColumn('info_uk');
            }
        });
    }
}
