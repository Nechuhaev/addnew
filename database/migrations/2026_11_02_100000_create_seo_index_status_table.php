<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Статус індексації Google для оголошень, категорій і тегів (seo:index-status):
 * з Search Analytics (є покази → в індексі) і URL Inspection API (вердикт + причина).
 */
class CreateSeoIndexStatusTable extends Migration
{
    public function up()
    {
        Schema::create('seo_index_status', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('entity_type', 20);                   // ad / category / tag
            $table->unsignedBigInteger('entity_id');
            $table->string('url', 500);
            $table->string('state', 20);                         // indexed / not_indexed / excluded / unknown
            $table->string('verdict', 30)->nullable();           // PASS / NEUTRAL / FAIL (URL Inspection)
            $table->string('coverage_state', 255)->nullable();   // напр. «Crawled - currently not indexed»
            $table->unsignedInteger('impressions')->nullable();  // покази за 90 днів (Search Analytics)
            $table->string('source', 20);                        // analytics / inspection
            $table->timestamp('last_crawl_time')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->unique(['entity_type', 'entity_id']);
            $table->index(['entity_type', 'state']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('seo_index_status');
    }
}
