<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProdutoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('produto', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('usuario_id');

            $table->string('nome', 128);
            $table->integer('custo');
            $table->decimal('preco_unitario', 8, 2);
            $table->float('quantidade', 8, 2)->nullable();
            $table->unsignedBigInteger('tipo_produto_id');
            $table->text('descricao');
            $table->string('imagem', 128)->nullable();
            

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
        Schema::table('produto', function (Blueprint $table) {
            $table->dropForeign('produto_usuario_id_foreign');
        });
        Schema::dropIfExists('produto');
        
    }
}
