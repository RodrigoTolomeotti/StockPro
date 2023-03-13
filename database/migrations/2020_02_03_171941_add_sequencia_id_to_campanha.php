<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSequenciaIdToCampanha extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campanha', function (Blueprint $table) {
            $table->unsignedBigInteger('sequencia_id')->after('nome')->nullable();
            $table->foreign('sequencia_id')->references('id')->on('sequencia');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campanha', function (Blueprint $table) {
            $table->dropForeign('campanha_sequencia_id_foreign');
            $table->dropColumn('sequencia_id');
        });
    }
}
