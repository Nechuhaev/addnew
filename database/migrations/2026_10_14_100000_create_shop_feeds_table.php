<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Автооновлення товарів магазину з фіда за URL (App\ShopFeed, feeds:sync).
 * imports: категорія/місто для нових товарів і зв'язок з фідом;
 * ads.import_images_hash — щоб не перезавантажувати фото, якщо вони не змінились.
 */
class CreateShopFeedsTable extends Migration
{
    public function up()
    {
        Schema::create('shop_feeds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('url', 1000);
            $table->unsignedSmallInteger('frequency_hours')->default(24);
            $table->unsignedInteger('category_id')->nullable();
            $table->unsignedInteger('city_id')->nullable();
            $table->boolean('enabled')->default(true);
            // idle / running / ok / failed
            $table->string('status', 20)->default('idle');
            $table->text('last_error')->nullable();
            $table->unsignedSmallInteger('fail_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamps();

            $table->index(['enabled', 'next_run_at']);
        });

        Schema::table('imports', function (Blueprint $table) {
            $table->unsignedBigInteger('feed_id')->nullable()->after('user_id');
            $table->unsignedInteger('category_id')->nullable()->after('feed_id');
            $table->unsignedInteger('city_id')->nullable()->after('category_id');
            $table->unsignedInteger('missing_count')->default(0)->after('error_count');
            $table->index('feed_id');
        });

        Schema::table('ads', function (Blueprint $table) {
            $table->string('import_images_hash', 32)->nullable()->after('import_key');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shop_feeds');
        Schema::table('imports', function (Blueprint $table) {
            $table->dropIndex(['feed_id']);
            $table->dropColumn(['feed_id', 'category_id', 'city_id', 'missing_count']);
        });
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn('import_images_hash');
        });
    }
}
