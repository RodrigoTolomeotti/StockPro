<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableClientes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cliente', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('usuario_id');

            $table->string('nome', 128);
            $table->string('telefone', 128)->nullable();
            $table->string('email', 128)->nullable();
            $table->string('endereco', 128)->nullable();
            $table->string('cpf_cnpj', 14);
            

            $table->datetime('data_criacao');
            $table->datetime('data_atualizacao');

            $table->foreign('usuario_id')->references('id')->on('usuario');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cliente');
    }
}
