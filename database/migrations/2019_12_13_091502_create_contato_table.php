<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContatoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contato', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('usuario_id');

            $table->string('nome', 128);
            $table->string('empresa', 128);
            $table->string('email', 128);
            $table->string('telefone', 128)->nullable();

            $table->unsignedBigInteger('cargo_id')->nullable();
            $table->unsignedBigInteger('departamento_id')->nullable();
            $table->unsignedBigInteger('profissao_id')->nullable();
            $table->unsignedBigInteger('grupo_contato_id')->nullable();

            $table->string('facebook_link', 128)->nullable();
            $table->string('linkedin_link', 128)->nullable();
            $table->string('instagram_link', 128)->nullable();
            $table->string('twitter_link', 128)->nullable();

            $table->datetime('data_criacao');
            $table->datetime('data_atualizacao');

            $table->foreign('usuario_id')->references('id')->on('usuario');
            $table->foreign('cargo_id')->references('id')->on('cargo');
            $table->foreign('departamento_id')->references('id')->on('departamento');
            $table->foreign('profissao_id')->references('id')->on('profissao');
            $table->foreign('grupo_contato_id')->references('id')->on('grupo_contato');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contato');
    }
}
