<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDicaMotivoBloqueio extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
         Schema::table('motivo_bloqueio', function (Blueprint $table) {
             $table->text('dica')->nullable();
         });
     }

     /**
      * Reverse the migrations.
      *
      * @return void
      */
     public function down()
     {
         Schema::table('motivo_bloqueio', function (Blueprint $table) {
             $table->dropColumn('dica');
         });
     }
}
