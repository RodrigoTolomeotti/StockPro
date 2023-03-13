<?php

namespace App\Console\Commands;

use App\Usuario;
use App\Notificacao;
use Illuminate\Support\Facades\Hash;

use Illuminate\Console\Command;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create {nome} {email} {senha} {nivel_usuario}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a user';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param  \App\DripEmailer  $drip
     * @return mixed
     */
    public function handle()
    {

        try {

            \DB::beginTransaction();

            $usuario = Usuario::create([
                'nome' => $this->argument('nome'),
                'email' => $this->argument('email'),
                'senha' => Hash::make($this->argument('senha')),
                'nivel_usuario_id' => $this->argument('nivel_usuario')
            ]);

            Notificacao::create([
                'titulo' => 'Bem vindo ao Climb 🎉🎉🎉',
                'mensagem' => "Estamos felizes em saber que ganhamos mais um colaborador\n\nAgora você já pode dar os primeiros passos e começar a converter seus leads\n\nPrimeiro crie seus contatos, configure suas campanhas e é só esperar o cliente vir\n\nFique a vontade para explorar 😊",
                'usuario_id' => $usuario->id
            ]);

            \DB::commit();

            $this->info("User created");

        } catch (\Exception $e) {

            \DB::rollBack();

            $this->error($e->getMessage());

        }


    }
}
