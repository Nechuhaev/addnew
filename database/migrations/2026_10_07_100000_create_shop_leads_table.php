<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Кандидати в магазини: інтернет-магазини, яких адмін вручну відібрав для
 * запрошення на addnew (назва, сайт, контактний email із сайту, статус).
 */
class CreateShopLeadsTable extends Migration
{
    public function up()
    {
        Schema::create('shop_leads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('site_url');
            $table->string('domain')->unique();
            $table->string('category')->nullable();
            $table->string('email')->nullable();
            $table->string('email_source', 20)->nullable(); // site | manual
            $table->string('email_lookup_status', 20)->nullable(); // ok | not_found | unreachable
            $table->string('status', 20)->default('new'); // new | contacted | replied | joined | declined
            $table->text('note')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        // Листи-запрошення, надіслані кандидатам із сайту (як shop_messages для магазинів)
        Schema::create('shop_lead_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('shop_lead_id');
            $table->unsignedBigInteger('admin_user_id')->nullable();
            $table->string('subject');
            $table->text('body');
            $table->string('sent_to_email');
            $table->boolean('sent_successfully')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('shop_lead_id')->references('id')->on('shop_leads')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shop_lead_messages');
        Schema::dropIfExists('shop_leads');
    }
}
