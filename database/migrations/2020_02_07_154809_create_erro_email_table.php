<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateErroEmailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('erro_email', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('titulo', 128);
            $table->string('mensagem', 256);
            $table->unsignedBigInteger('usuario_id');

            $table->foreign('usuario_id')->references('id')->on('usuario');

            $table->datetime('data_visualizacao')->nullable();;
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
        Schema::dropIfExists('erro_email');
    }
}
