<?php

use Illuminate\Database\Seeder;
use App\Origem;

class OrigemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Origem::create(['id' => '1', 'nome' => 'Email', 'icone_classe' => 'far fa-envelope', 'icone_cor' => '#65a2ff', 'ordem' => '1']);
        Origem::create(['id' => '2', 'nome' => 'Facebook', 'icone_classe' => 'fab fa-facebook', 'icone_cor' => 'rgb(64, 93, 155)', 'ordem' => '2']);
        Origem::create(['id' => '3', 'nome' => 'Linkedin', 'icone_classe' => 'fab fa-linkedin', 'icone_cor' => 'rgb(0, 115, 177)', 'ordem' => '3']);
        Origem::create(['id' => '4', 'nome' => 'Telefone', 'icone_classe' => 'fas fa-phone', 'icone_cor' => '#65a2ff', 'ordem' => '4']);
        Origem::create(['id' => '5', 'nome' => 'Telegram', 'icone_classe' => 'fab fa-telegram', 'icone_cor' => 'rgb(58, 109, 153)', 'ordem' => '5']);
        Origem::create(['id' => '6', 'nome' => 'Whatsapp', 'icone_classe' => 'fab fa-whatsapp', 'icone_cor' => 'rgb(7, 188, 76)', 'ordem' => '6']);
        Origem::create(['id' => '7', 'nome' => 'Outros', 'icone_classe' => 'far fa-question-circle', 'icone_cor' => '#65a2ff', 'ordem' => '7']);
    }
}
