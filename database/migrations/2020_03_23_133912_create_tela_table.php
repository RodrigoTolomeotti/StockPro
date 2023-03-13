<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTelaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tela', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->string('nome', 128);
            $table->string('url', 128);
            $table->string('icone_classe', 128)->nullable();
            $table->tinyInteger('menu_destino')->nullable()->comment('1 - sidebar menu, 2 - user menu, 3 - admin menu');

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
        Schema::dropIfExists('tela');
    }
}
