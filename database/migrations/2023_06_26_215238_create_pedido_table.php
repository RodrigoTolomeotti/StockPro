<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedido', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('cliente_id');

            $table->decimal('valor_total', 8, 2);

            $table->datetime('data_liberacao');
            $table->datetime('data_entrega');
            
            $table->datetime('data_criacao');
            $table->datetime('data_atualizacao');

            $table->foreign('usuario_id')->references('id')->on('usuario');
            $table->foreign('cliente_id')->references('id')->on('cliente');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->dropForeign('pedido_usuario_id_foreign');
            $table->dropForeign('pedido_cliente_id_foreign');
        });
        Schema::dropIfExists('pedido');
    }
}
