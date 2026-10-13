<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Email користувача може бути порожнім: у магазинів, чий email не підтвердився на їхньому
 * сайті, його прибирають (shops:apply-email-check --clear-not-found), а адмін додає вручну.
 * Унікальність лишається (кілька NULL у MySQL допустимі).
 */
class MakeUsersEmailNullable extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NULL');
    }

    public function down()
    {
        DB::statement("UPDATE users SET email = CONCAT('no-email-', id, '@invalid.local') WHERE email IS NULL");
        DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NOT NULL');
    }
}
