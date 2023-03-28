<?php

use Illuminate\Database\Seeder;
use App\NivelUsuario;

class NivelUsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $nivel_usuario = NivelUsuario::create(['id' => '1', 'nome' => 'Usuário']);

        $nivel_usuario->telas()->attach(1);
        $nivel_usuario->telas()->attach(2);
        $nivel_usuario->telas()->attach(3);

    }
}
