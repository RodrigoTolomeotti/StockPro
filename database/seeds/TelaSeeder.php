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
        Tela::create(['id' => '2', 'nome' => 'Configurações gerais', 'url' => 'configuracoes', 'menu_destino' => '2']);
        Tela::create(['id' => '3', 'nome' => 'Clientes', 'url' => 'clientes', 'menu_destino' => '1', 'icone_classe' => 'fas fa-users']);
        Tela::create(['id' => '4', 'nome' => 'Produtos', 'url' => 'produtos', 'menu_destino' => '1', 'icone_classe' => 'fab fa-product-hunt']);
        Tela::create(['id' => '5', 'nome' => 'Tipos de Produtos', 'url' => 'tipos_prrodutos', 'menu_destino' => '1', 'icone_classe' => 'fas fa-box']);
    }
}
