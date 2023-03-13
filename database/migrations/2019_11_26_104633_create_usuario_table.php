<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuarioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuario', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nome', 128);
            $table->string('email', 128)->unique();
            $table->text('senha');
            $table->string('token', 256)->nullable();

            # SMTP data
            $table->string('smtp_host', 256)->nullable();
            $table->unsignedSmallInteger('smtp_port')->nullable();
            $table->boolean('smtp_ssl')->nullable();

            # IAMP data
            $table->string('imap_host', 256)->nullable();
            $table->unsignedSmallInteger('imap_port')->nullable();
            $table->boolean('imap_ssl')->nullable();

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
        Schema::dropIfExists('usuario');
    }
}
