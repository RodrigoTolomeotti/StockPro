<?php

namespace App\Http\Controllers;

use App\Envio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Auth;

class EnvioController extends Controller
{

    private $user = null;

    public function __construct() {
        $this->user = Auth::user();
    }

    public function gravarAbertura($token, Request $request)
    {

        $token = explode('-', $token);
        // $dados = array($usuario->id, $campanha_id, $template->sequencia_id, $template->template_id, $emailEnviado->id, $emailEnviado->data_criacao->format('d/m/Y H:i:s'));
        if(sizeof($token) == 5) {
            //Separação dos dados em variáveis diferentes
            $retorno_usuario_id = $token[0];
            $retorno_campanha_id = $token[1];
            $retorno_sequencia_id = $token[2];
            $retorno_template_id = $token[3];
            $envio_id = $token[4];
            $retorno_data_criacao = $token[5];
            //Construção da WHERE clause.
            $where = " usuario.id = $retorno_usuario_id
                AND campanha.id = $retorno_campanha_id
                AND envio.template_id = $retorno_template_id
                AND sequencia.id = $retorno_sequencia_id
            ";
        } else {
            $envio_id = Crypt::decryptString($token);
        }

        // Busca o id e email do usuário que enviou o e-mail
        // Verificação pra ver se realmente existe o envio conferindo todos os relacionamentos da chave.
        if($where != '' || $where != null) {
            $envio_info = Envio::select('usuario.id as usuario_id', 'contato.email as contato_email','envio.id as id')
                ->join('sequencia', 'envio.sequencia_id', 'sequencia.id')
                ->join('contato', 'envio.contato_id', 'contato.id')
                ->join('campanha', 'sequencia.campanha_id', 'campanha.id')
                ->join('usuario', 'campanha.usuario_id', 'usuario.id')
                ->where('envio.id', $envio_id)
                ->whereRaw($where)
                ->first();
        } else {
            $envio_info = Envio::select('usuario.id as usuario_id', 'contato.email as contato_email', 'envio.id as envio')
                ->join('sequencia', 'envio.sequencia_id', 'sequencia.id')
                ->join('contato', 'envio.contato_id', 'contato.id')
                ->join('campanha', 'sequencia.campanha_id', 'campanha.id')
                ->join('usuario', 'campanha.usuario_id', 'usuario.id')
                ->where('envio.id', $envio_id)->first();
        }
        if($envio_info) {
            $envio = Envio::query()->where('id', $envio_info->id)->first();
            if (!$envio->data_abertura) {

                $envio->data_abertura = \DB::raw('now()');
                $envio->save();
            }
            // return new BinaryFileResponse(storage_path('/images/receive.jpg'), 200, ['Content-Type' => 'image/png']);
            return new BinaryFileResponse(storage_path('/images/receive.jpg'), 200, ['Content-Type' => 'image/png']);
        }

    }

    public function getAll(Request $request){

        try{

            $query = Envio::query()->select('envio.data_criacao AS envio',
                                            'contato.nome AS contato',
                                            'contato.empresa AS empresa',
                                            (\DB::raw("CONCAT((SELECT ordem FROM sequencia_template WHERE sequencia_id = envio.sequencia_id AND template_id = envio.template_id), '/', (SELECT COUNT(*) FROM sequencia_template WHERE sequencia_id = envio.sequencia_id)) AS conteudo")),
                                            'envio.data_abertura',
                                            'envio.data_bounce',
                                            'campanha.nome',
                                            'template.assunto',
                                            'template.mensagem')
                                     ->join('contato', 'contato.id', '=', 'envio.contato_id')
                                     ->join('sequencia', 'sequencia.id', '=', 'envio.sequencia_id')
                                     ->join('campanha', 'campanha.id', '=', 'sequencia.campanha_id')
                                     ->join('template', 'template.id', '=', 'envio.template_id')
                                     ->where('campanha.usuario_id', $this->user->id)
                                     ->orderBy('envio.data_criacao', 'desc');

            if ($request->has('campanha_id') && $request->input('campanha_id') != '') {
                $query->where('sequencia.campanha_id', '=', $request->input('campanha_id'));
            }

            if ($request->has('data_inicio') && $request->input('data_inicio') != '') {
                $query->whereDate('envio.data_criacao', '>=', $request->input('data_inicio'));
            }

            if ($request->has('data_fim') && $request->input('data_fim') != '') {
                $query->whereDate('envio.data_criacao', '<=', $request->input('data_fim'));
            }

            if($request->has('campanha_idClick')) {
                $query->where('campanha.id', $request->campanha_idClick);
            }

            if($request->has('data_abertura')) {
                $query->whereNotNull('envio.data_abertura');
            }

            $total_rows =  $query->count();

            if ($request->has('offset')) {
                $offset = $request->input('offset');
                $query->offset($offset);
            }

            if ($request->has('limit')) {
                $limit = $request->input('limit');
                $query->limit($limit);
            }

            if ($request->has('sort_by')) {
                $sort_by = $request->input('sort_by');
                $order_by = $request->input('order_by', 'asc');
                $query->orderBy($sort_by, $order_by);
            }


            return ['data' => $query->get(),
                    'totalRows' => $total_rows];

        }catch(\Exception $e){
            return response()->json([
                'errors' => [$e->getMessage()]
            ]);
        }
    }

}
