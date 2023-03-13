<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdjustUsuario extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->string('imagem', 128)->nullable();
            $table->string('conta_email', 128)->nullable();
            $table->renameColumn('email_usuario', 'conta_usuario');
            $table->renameColumn('email_senha', 'conta_senha');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->renameColumn('conta_senha', 'email_senha');
            $table->renameColumn('conta_usuario', 'email_usuario');
            $table->dropColumn('conta_email');
            $table->dropColumn('imagem');
        });
    }
}
