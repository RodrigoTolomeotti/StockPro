<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableEstoque extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('estoque', function (Blueprint $table) {
            $table->decimal('custo', 8, 2);
            $table->integer('tipo_estoque')->comment('(1) Entrada | (2) Saída');
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
            $table->dropColumn('custo');
        });
    }
}
