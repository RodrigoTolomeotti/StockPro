<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterClienteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cliente', function (Blueprint $table) {
            $table->string('cpf_cnpj', 14)->nullable(true)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cliente', function (Blueprint $table) {
            $table->dropUnique(['cpf_cnpj']);
        });
    }
}
