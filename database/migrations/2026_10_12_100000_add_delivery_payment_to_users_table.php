<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Доставка й оплата магазину (див. App\Services\ShopDelivery).
 */
class AddDeliveryPaymentToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('delivery_methods')->nullable();
            $table->text('payment_methods')->nullable();
            $table->unsignedInteger('free_delivery_from')->nullable();
            $table->string('delivery_note', 500)->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['delivery_methods', 'payment_methods', 'free_delivery_from', 'delivery_note']);
        });
    }
}
