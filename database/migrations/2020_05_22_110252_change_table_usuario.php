<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTableUsuario extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->string('smtp_security', 3)->nullable()->after('smtp_port');
            $table->string('imap_security', 3)->nullable()->after('imap_port');
        });

        \DB::statement('update usuario set smtp_security = \'ssl\' where smtp_ssl = 1');
        \DB::statement('update usuario set imap_security = \'ssl\' where imap_ssl = 1');

        Schema::table('usuario', function (Blueprint $table) {
            $table->dropColumn('smtp_ssl');
            $table->dropColumn('imap_ssl');
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
            $table->boolean('smtp_ssl')->nullable()->after('smtp_port');
            $table->boolean('imap_ssl')->nullable()->after('imap_port');
        });

        \DB::statement('update usuario set smtp_ssl = 1 where smtp_security = \'ssl\'');
        \DB::statement('update usuario set imap_ssl = 1 where imap_security = \'ssl\'');

        Schema::table('usuario', function (Blueprint $table) {
            $table->dropColumn('smtp_security');
            $table->dropColumn('imap_security');
        });

    }
}
