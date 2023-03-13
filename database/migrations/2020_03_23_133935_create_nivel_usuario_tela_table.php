<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNivelUsuarioTelaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nivel_usuario_tela', function (Blueprint $table) {

            $table->unsignedBigInteger('nivel_usuario_id');
            $table->foreign('nivel_usuario_id')->references('id')->on('nivel_usuario');

            $table->unsignedBigInteger('tela_id');
            $table->foreign('tela_id')->references('id')->on('tela');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nivel_usuario_tela');
    }
}
