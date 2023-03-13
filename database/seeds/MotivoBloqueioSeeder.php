<?php

use Illuminate\Database\Seeder;
use App\MotivoBloqueio;

class MotivoBloqueioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        MotivoBloqueio::create(['id' => '1', 'descricao' => 'Bounce', 'dica' => 'Verifique se o email do contato está correto.']);
        MotivoBloqueio::create(['id' => '2', 'descricao' => 'Erro ao enviar', 'dica' => 'Verifique se o seu smtp está correto e funcionando.']);
    }
}
