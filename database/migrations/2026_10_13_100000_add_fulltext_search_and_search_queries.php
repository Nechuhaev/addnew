<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Повнотекстовий пошук оголошень (App\Services\AdSearch) і журнал
 * пошукових запитів для статистики в адмінці (App\SearchQuery).
 */
class AddFulltextSearchAndSearchQueries extends Migration
{
    public function up()
    {
        $exists = DB::select("SHOW INDEX FROM ads WHERE Key_name = 'ads_fulltext'");
        if (!$exists) {
            DB::statement('ALTER TABLE ads ADD FULLTEXT ads_fulltext (name, content, brand)');
        }

        Schema::create('search_queries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('query', 191);
            $table->unsignedInteger('results')->default(0);
            // site — загальний пошук, stores — список магазинів, shop — товари одного магазину
            $table->string('source', 20)->default('site');
            $table->unsignedBigInteger('shop_id')->nullable();
            $table->string('locale', 5)->default('uk');
            $table->boolean('is_auth')->default(false);
            $table->timestamp('created_at')->nullable();

            $table->index('created_at');
            $table->index(['query', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('search_queries');
        if (DB::select("SHOW INDEX FROM ads WHERE Key_name = 'ads_fulltext'")) {
            DB::statement('ALTER TABLE ads DROP INDEX ads_fulltext');
        }
    }
}
