<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Email користувача може бути порожнім: у магазинів, чий email не підтвердився на їхньому
 * сайті, його прибирають (shops:apply-email-check --clear-not-found), а адмін додає вручну.
 * Унікальність лишається (кілька NULL у MySQL допустимі).
 *
 * Тип, кодування й collation колонки беруться з поточних (information_schema): MODIFY без
 * них перевів би колонку в кодування таблиці (utf8mb4), і унікальний індекс на VARCHAR(255)
 * перевищив би ліміт MyISAM у 1000 байт.
 */
class MakeUsersEmailNullable extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE users MODIFY email ' . $this->columnDefinition() . ' NULL');
    }

    public function down()
    {
        DB::statement("UPDATE users SET email = CONCAT('no-email-', id, '@invalid.local') WHERE email IS NULL");
        DB::statement('ALTER TABLE users MODIFY email ' . $this->columnDefinition() . ' NOT NULL');
    }

    protected function columnDefinition(): string
    {
        $col = DB::selectOne(
            'SELECT COLUMN_TYPE, CHARACTER_SET_NAME, COLLATION_NAME FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            ['users', 'email']
        );

        $definition = $col->COLUMN_TYPE ?? 'varchar(255)';
        if (!empty($col->CHARACTER_SET_NAME)) {
            $definition .= ' CHARACTER SET ' . $col->CHARACTER_SET_NAME;
        }
        if (!empty($col->COLLATION_NAME)) {
            $definition .= ' COLLATE ' . $col->COLLATION_NAME;
        }

        return $definition;
    }
}
