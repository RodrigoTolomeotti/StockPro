<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBloqueioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bloqueio', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('descricao', 256);
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('contato_id');
            $table->unsignedBigInteger('motivo_bloqueio_id');

            $table->foreign('usuario_id')->references('id')->on('usuario');
            $table->foreign('contato_id')->references('id')->on('contato');
            $table->foreign('motivo_bloqueio_id')->references('id')->on('motivo_bloqueio');

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
        Schema::dropIfExists('bloqueio');
    }
}
