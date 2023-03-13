<?php

use Illuminate\Database\Seeder;
use App\Usuario;
use App\Notificacao;

class CreateNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $usuarios = Usuario::query()->get();
        $titulo = "Atualização 🎉🎉";
        $mensagem .= "Olá, correção realizada na busca dos e-mails.";
        $mensagem .= "<br><br><b>Atenciosamente equipe Climb.<b>";

        foreach ($usuarios as $key => $usuario) {

            Notificacao::create([
                'titulo' => $titulo,
                'mensagem' => $mensagem,
                'usuario_id' => $usuario->id
            ]);

        }

    }
}
