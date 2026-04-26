<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImportsTable extends Migration
{
    public function up()
    {
        Schema::create('imports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('file_path');
            $table->string('status')->default('pending');
            $table->integer('total')->default(0);
            $table->integer('processed')->default(0);
            $table->integer('new_count')->default(0);
            $table->integer('update_count')->default(0);
            $table->integer('error_count')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('imports');
    }
}
