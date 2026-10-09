<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Обране користувача + сповіщення про зниження ціни.
 * base_price_uah — ціна (у грн), від якої рахуємо зниження: ціна на момент
 * додавання, далі — ціна з останнього листа або нова вища ціна.
 */
class CreateFavoritesTable extends Migration
{
    public function up()
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('ad_id');
            $table->decimal('price_at_add_uah', 12, 2)->default(0);
            $table->decimal('base_price_uah', 12, 2)->default(0);
            $table->boolean('notify')->default(true);
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'ad_id']);
            $table->index('ad_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('price_drop_emails')->default(true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('favorites');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('price_drop_emails');
        });
    }
}
