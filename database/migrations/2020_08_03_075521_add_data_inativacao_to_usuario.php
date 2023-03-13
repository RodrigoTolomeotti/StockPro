<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDataInativacaoToUsuario extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
         Schema::table('usuario', function (Blueprint $table) {
             $table->datetime('data_inativacao')->nullable();
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
             $table->dropColumn('data_inativacao');
         });
     }
}
