<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterEstoqueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('estoque', function (Blueprint $table) {
            $table->unsignedBigInteger('fornecedor_id');

            $table->foreign('fornecedor_id')->references('id')->on('fornecedor');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('estoque', function (Blueprint $table) {
            $table->dropForeign('estoque_fornecedor_id_foreign');
            $table->dropColumn('fornecedor_id');
        });
    }
}
