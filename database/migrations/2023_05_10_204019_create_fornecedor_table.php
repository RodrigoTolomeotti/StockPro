<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFornecedorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fornecedor', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('usuario_id');

            $table->string('cpf_cnpj', 14)->nullable()->unique();
            
            $table->string('nome', 256);
            $table->string('telefone', 14);
            $table->string('email', 256);
            $table->string('endereco', 256);

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
        Schema::table('fornecedor', function (Blueprint $table) {
            $table->dropForeign('fornecedor_usuario_id_foreign');
            $table->dropUnique(['cpf_cnpj']);
        });
        Schema::dropIfExists('fornecedor');
    }
}
