<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Usuario;
use App\Notificacao;

class DevelopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $usuarios = [
            ['nome' => 'Rodrigo Tolomeotti', 'email' => 'rodrigo@gmail.com', 'senha' => Hash::make('jaca'), 'nivel_usuario_id' => 1],
            ['nome' => 'João Marcos', 'email' => 'joao@gmail.com', 'senha' => Hash::make('whey'), 'nivel_usuario_id' => 1],
        ];

        $titulo = 'Bem vindo ao StockPro 🎉🎉🎉';
        $mensagem = "Estamos felizes em saber que ganhamos mais um colaborador<br><br>Agora você já pode dar os primeiros passos e começar a converter seus clientes<br><br>Primeiro crie seus contatos, faça seus pedidos e pronto!<br><br>Fique a vontade para explorar 😊";

        foreach ($usuarios as $key => $usuario) {

            $usuario = Usuario::create($usuario);

            Notificacao::create([
                'titulo' => $titulo,
                'mensagem' => $mensagem,
                'usuario_id' => $usuario->id
            ]);

        }

        factory(App\Cliente::class, 10)->create();
        factory(App\TipoProduto::class, 5)->create();
        factory(App\Fornecedor::class, 5)->create();
        factory(App\Produto::class, 5)->create();

    }
}
