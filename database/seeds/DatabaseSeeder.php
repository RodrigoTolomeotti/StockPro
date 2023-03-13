<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $this->call('CargoSeeder');
        $this->call('DepartamentoSeeder');
        $this->call('ProfissaoSeeder');
        $this->call('OrigemSeeder');
        $this->call('TelaSeeder');
        $this->call('NivelUsuarioSeeder');
        if (getenv('APP_ENV') == 'local') $this->call('DevelopSeeder');

    }
}
