<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopReviewsTable extends Migration
{
    /**
     * Відгуки магазинів — будь-який зареєстрований користувач може
     * залишити ОДИН відгук на магазин (unique по shop_user_id +
     * reviewer_user_id). Повторна спроба оновлює наявний відгук, а не
     * створює дублікат.
     */
    public function up()
    {
        Schema::create('shop_reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('shop_user_id');
            $table->unsignedInteger('reviewer_user_id');
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['shop_user_id', 'reviewer_user_id']);
            $table->index('shop_user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shop_reviews');
    }
}
