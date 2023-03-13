<?php

namespace App\Http\Controllers;

use App\Notificacao;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class NotificacaoController extends Controller
{

    private $user = null;

    public function __construct() {
        $this->user = Auth::user();
    }

    public function getAll()
    {

        $x = \DB::select("
            select titulo,
            count(*) total,
            max(data_criacao) data,
            COUNT(data_visualizacao) visualizacoes
            from notificacao
            where data_criacao > DATE_SUB(NOW(), INTERVAL 1 WEEK)
              and usuario_id = '" . $this->user->id . "'
            group by titulo
            order by data desc
        ");

        return ['data' => $x];
        return ['data' => $this->user->notificacoes()->orderBy('data_criacao', 'DESC')->limit(20)->get()];

    }

    public function markAllAsRead(Request $request) {

        foreach ($request->input('notificacoes') as $notificacao) {

            $n = $this->user->notificacoes()->find($notificacao);

            $n->data_visualizacao = \DB::raw('NOW()');

            $n->save();

        }

    }

    public function findByTitulo($titulo) {

        $notificacoes = $this->user->notificacoes()->where('titulo', urldecode($titulo))->get();

        foreach ($notificacoes as $notificacao) {

            $notificacao->data_visualizacao = \DB::raw('NOW()');

            $notificacao->save();

        }

        return ['data' => $notificacoes];

    }

}
