<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Історія ціни оголошень (графік на сторінці товару).
 * Одна точка на оголошення за день — остання ціна цього дня.
 *
 * Стартові дані:
 *  1) зміни, які вже застосував моніторинг цін (product_price_checks.price_applied);
 *  2) поточна ціна активних оголошень, якщо вона відрізняється від останньої точки.
 */
class CreateAdPriceHistoryTable extends Migration
{
    public function up()
    {
        Schema::create('ad_price_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Без foreign key — ads має ENGINE=MyISAM.
            $table->unsignedBigInteger('ad_id');
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('currency_id')->nullable();
            $table->decimal('price_uah', 12, 2);
            $table->date('recorded_on');

            $table->unique(['ad_id', 'recorded_on']);
        });

        $rate = 'COALESCE(cur.rate, 1)';

        // 1a) Ціна ДО першої застосованої перевірки — на день раніше (або на дату створення)
        DB::statement("
            INSERT IGNORE INTO ad_price_history (ad_id, price, currency_id, price_uah, recorded_on)
            SELECT c.ad_id, c.old_price, a.currency_id, c.old_price * {$rate},
                   LEAST(DATE(COALESCE(a.created_at, c.checked_at)), DATE(c.checked_at) - INTERVAL 1 DAY)
            FROM product_price_checks c
            JOIN (SELECT ad_id, MIN(id) AS id FROM product_price_checks
                  WHERE price_applied = 1 AND old_price > 0 GROUP BY ad_id) f ON f.id = c.id
            JOIN ads a ON a.id = c.ad_id
            LEFT JOIN ad_currencies cur ON cur.id = a.currency_id
        ");

        // 1b) Кожна застосована зміна — на дату перевірки (остання за день перемагає)
        DB::statement("
            INSERT INTO ad_price_history (ad_id, price, currency_id, price_uah, recorded_on)
            SELECT c.ad_id, c.found_price, a.currency_id, c.found_price * {$rate}, DATE(c.checked_at)
            FROM product_price_checks c
            JOIN ads a ON a.id = c.ad_id
            LEFT JOIN ad_currencies cur ON cur.id = a.currency_id
            WHERE c.price_applied = 1 AND c.found_price > 0
            ORDER BY c.id
            ON DUPLICATE KEY UPDATE price = VALUES(price), price_uah = VALUES(price_uah)
        ");

        // 2) Поточна ціна, якщо історії немає або остання точка інша
        DB::statement("
            INSERT INTO ad_price_history (ad_id, price, currency_id, price_uah, recorded_on)
            SELECT a.id, a.price, a.currency_id, a.price * {$rate}, CURDATE()
            FROM ads a
            LEFT JOIN ad_currencies cur ON cur.id = a.currency_id
            LEFT JOIN (
                SELECT h.ad_id, h.price, h.currency_id FROM ad_price_history h
                JOIN (SELECT ad_id, MAX(recorded_on) d FROM ad_price_history GROUP BY ad_id) m
                  ON m.ad_id = h.ad_id AND m.d = h.recorded_on
            ) last ON last.ad_id = a.id
            WHERE a.status = 1 AND a.price > 0
              AND (last.ad_id IS NULL OR last.price <> a.price OR NOT (last.currency_id <=> a.currency_id))
            ON DUPLICATE KEY UPDATE price = VALUES(price), currency_id = VALUES(currency_id), price_uah = VALUES(price_uah)
        ");
    }

    public function down()
    {
        Schema::dropIfExists('ad_price_history');
    }
}
