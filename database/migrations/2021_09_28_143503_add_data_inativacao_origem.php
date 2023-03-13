<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDataInativacaoOrigem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('origem', function (Blueprint $table) {
            $table->datetime('data_inativacao')->nullable()->after('data_atualizacao');

        });
        Schema::table('origem', function (Blueprint $table) {
            \DB::statement('update origem set data_inativacao = now() where id not in (1,6)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('origem', function (Blueprint $table) {
            $table->dropColumn('data_inativacao');
        });
    }
}
