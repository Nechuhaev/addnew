<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Журнал відправок на індексацію (seo:submit-index, seo:indexnow): що, куди і коли надіслано —
 * для звіту «надіслано / з надісланих в індексі» і щоб не надсилати те саме щодня.
 */
class CreateSeoIndexSubmissionsTable extends Migration
{
    public function up()
    {
        Schema::create('seo_index_submissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('entity_type', 20);          // ad / category / tag / article
            $table->unsignedBigInteger('entity_id');
            $table->string('channel', 20);              // indexnow / google
            $table->string('url', 500);
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedInteger('times')->default(1);

            $table->unique(['entity_type', 'entity_id', 'channel'], 'seo_submissions_entity_channel');
            $table->index(['channel', 'submitted_at']);
        });

        Schema::table('daily_reports', function (Blueprint $table) {
            $table->unsignedInteger('index_submitted_indexnow')->nullable();
            $table->unsignedInteger('index_submitted_google')->nullable();
            $table->text('index_submitted_summary')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('seo_index_submissions');
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropColumn(['index_submitted_indexnow', 'index_submitted_google', 'index_submitted_summary']);
        });
    }
}
