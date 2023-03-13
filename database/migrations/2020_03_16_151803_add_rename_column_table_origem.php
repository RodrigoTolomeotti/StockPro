<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRenameColumnTableOrigem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('origem', function (Blueprint $table) {
            $table->renameColumn('icone', 'icone_classe');
            $table->string('icone_cor', 30)->after('icone');
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
            $table->renameColumn('icone_classe', 'icone');
            $table->dropColumn('icone_cor');            
        });
    }
}
