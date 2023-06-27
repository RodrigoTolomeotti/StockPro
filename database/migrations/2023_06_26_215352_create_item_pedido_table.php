<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemPedidoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_pedido', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('pedido_id');
            $table->unsignedBigInteger('produto_id');

            $table->decimal('preco_unitario', 8, 2);
            $table->decimal('desconto', 8, 2);
            $table->float('quantidade', 8, 2);

            $table->datetime('data_criacao');
            $table->datetime('data_atualizacao');

            $table->foreign('usuario_id')->references('id')->on('usuario');
            $table->foreign('pedido_id')->references('id')->on('pedido');
            $table->foreign('produto_id')->references('id')->on('produto');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_pedido', function (Blueprint $table) {
            $table->dropForeign('item_pedido_usuario_id_foreign');
            $table->dropForeign('item_pedido_pedido_id_foreign');
            $table->dropForeign('item_pedido_produto_id_foreign');
        });
        Schema::dropIfExists('item_pedido');
    }
}
