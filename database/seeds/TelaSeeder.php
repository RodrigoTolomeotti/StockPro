<?php

use Illuminate\Database\Seeder;
use App\Tela;

class TelaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Tela::create(['id' => '1', 'nome' => 'Início', 'url' => 'inicio', 'menu_destino' => '1', 'icone_classe' => 'fas fa-home']);
        Tela::create(['id' => '2', 'nome' => 'Grupos de contato', 'url' => 'grupos-contato', 'menu_destino' => '1', 'icone_classe' => 'fas fa-users']);
        Tela::create(['id' => '3', 'nome' => 'Contatos', 'url' => 'contatos', 'menu_destino' => '1', 'icone_classe' => 'fas fa-user']);
        Tela::create(['id' => '4', 'nome' => 'Campanhas', 'url' => 'campanhas', 'menu_destino' => '1', 'icone_classe' => 'far fa-envelope']);
        Tela::create(['id' => '5', 'nome' => 'Envios', 'url' => 'envios', 'menu_destino' => '1', 'icone_classe' => 'fas fa-paper-plane']);
        Tela::create(['id' => '6', 'nome' => 'Retornos', 'url' => 'retornos', 'menu_destino' => '1', 'icone_classe' => 'fas fa-envelope-open-text']);
        Tela::create(['id' => '7', 'nome' => 'Configurações gerais', 'url' => 'configuracoes', 'menu_destino' => '2']);
    }
}
