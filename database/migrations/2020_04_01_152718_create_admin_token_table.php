<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminTokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_token', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->unsignedBigInteger('admin_id');
            $table->foreign('admin_id')->references('id')->on('admin');

            $table->string('token', 256);
            $table->datetime('data_expiracao');
            
            $table->string('ip', 16)->nullable();
            $table->string('user_agent', 256)->nullable();

            $table->datetime('data_criacao');
            $table->datetime('data_atualizacao');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_token');
    }
}
