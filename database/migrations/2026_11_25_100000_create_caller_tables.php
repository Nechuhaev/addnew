<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI-дзвінки інтернет-магазинам: ліди, дзвінки, список «не дзвонити»,
 * листи з інструкцією та налаштування модуля (редагуються в адмінці).
 */
class CreateCallerTables extends Migration
{
    public function up()
    {
        Schema::create('caller_leads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('phone', 20)->unique();          // E.164: +380XXXXXXXXX
            $table->string('store_name')->default('');
            $table->string('store_site')->default('');
            $table->string('contact_name')->default('');
            $table->string('category')->default('');
            // new | calling | retry | callback | done | dnc | failed
            $table->string('status', 16)->default('new');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->dateTime('next_call_at')->nullable();
            $table->dateTime('last_called_at')->nullable();
            $table->string('conversation_id')->nullable();
            $table->string('outcome', 32)->nullable();
            $table->string('email')->nullable();
            $table->string('has_merchant_feed', 8)->default('unknown');
            $table->text('notes')->nullable();
            $table->string('source', 32)->default('csv');
            // Конверсія: магазин зареєструвався після дзвінка
            $table->unsignedBigInteger('registered_user_id')->nullable();
            $table->dateTime('registered_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'next_call_at']);
            $table->index('email');
        });

        Schema::create('caller_calls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('conversation_id')->unique();
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->dateTime('started_at');
            $table->date('call_date');
            // initiated | received (вебхук прийнято, чекає аналізу) | completed | failed
            $table->string('status', 16)->default('initiated');
            $table->unsignedInteger('duration_secs')->nullable();
            $table->string('outcome', 32)->nullable();
            $table->longText('analysis_json')->nullable();
            $table->longText('transcript_json')->nullable();
            $table->longText('raw_event')->nullable();
            $table->unsignedTinyInteger('process_attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['status', 'started_at']);
            $table->index('call_date');
            $table->index('lead_id');
        });

        // Номер потрапляє сюди назавжди, навіть якщо лід видалять і імпортують знову
        Schema::create('caller_do_not_call', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('phone', 20)->unique();
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('caller_emails', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lead_id');
            $table->string('to_email');
            $table->string('template', 32)->default('instruction');
            // pending | review | sent | failed | skipped
            $table->string('status', 16);
            $table->string('has_merchant_feed', 8)->default('unknown');
            $table->string('contact_name')->default('');
            $table->string('reason')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();

            // Один лист-інструкція на магазин, навіть якщо дзвонили кілька разів
            $table->unique(['lead_id', 'template']);
            $table->index(['status', 'sent_at']);
        });

        Schema::create('caller_settings', function (Blueprint $table) {
            $table->string('key', 64)->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('caller_settings');
        Schema::dropIfExists('caller_emails');
        Schema::dropIfExists('caller_do_not_call');
        Schema::dropIfExists('caller_calls');
        Schema::dropIfExists('caller_leads');
    }
}
