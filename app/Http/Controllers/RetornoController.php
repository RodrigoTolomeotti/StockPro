<?php

namespace App\Http\Controllers;

use App\Retorno;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RetornoController extends Controller
{

    private $user = null;

    public function __construct() {
        $this->user = Auth::user();
    }


    public function getAll(request $request) {
        $user = $this->user->id;

        $query = Retorno::query()
        ->select('retorno.id',
                'contato.nome',
                'retorno.data_criacao as data',
                'retorno.origem_id',
                'origem.nome as origem_nome',
                'template.assunto',
                'retorno.mensagem',
                'campanha.nome as campanha_nome',
                'contato.empresa',
                'retorno.ind_avaliacao',
                (\DB::raw("(select ordem from sequencia_template st where st.sequencia_id = envio.sequencia_id and st.template_id = envio.template_id) as sequencia_atual")),
                (\DB::raw("(select count(template_id) from sequencia_template st where st.sequencia_id = sequencia.id) as sequencia_maxima")),
                (\DB::raw("(select email from usuario where id = $user) as email_usuario")))
        ->join('envio', 'retorno.envio_id', '=', 'envio.id')
        ->join('contato', 'envio.contato_id', '=', 'contato.id')
        ->join('sequencia', 'envio.sequencia_id', '=', 'sequencia.id')
        ->join('campanha', 'sequencia.campanha_id', '=', 'campanha.id')
        ->join('template', 'envio.template_id', '=', 'template.id')
        ->join('origem', 'retorno.origem_id', '=', 'origem.id')
        ->where('contato.usuario_id', $user)
        ->orderBy('retorno.data_criacao', 'desc');

        if ($request->has('campanha_id') && $request->input('campanha_id') != '') {
            $query->where('sequencia.campanha_id', '=', $request->input('campanha_id'));
        }

        if ($request->has('data_inicio') && $request->input('data_inicio') != '') {
            $query->whereDate('retorno.data_criacao', '>=', $request->input('data_inicio'));
        }

        if ($request->has('data_fim') && $request->input('data_fim') != '') {
            $query->whereDate('retorno.data_criacao', '<=', $request->input('data_fim'));
        }

        if ($request->has('empresa') && $request->input('empresa') != '') {
            $query->where('contato.empresa', 'like', '%' . $request->input('empresa') . '%');
        }

        if ($request->has('ind_aval') && $request->input('ind_aval') != '') {

            if ($request->input('ind_aval') == 1) {
                $query->where('retorno.ind_avaliacao', '=', null);
            } else {
                $query->where('retorno.ind_avaliacao', '=', $request->input('ind_aval'));
            }
        }

        if ($request->has('grupos_contatos') && $request->input('grupos_contatos') != '') {
          $query->where('contato.grupo_contato_id', '=', $request->input('grupos_contatos'));
        }

        if($request->has('campanha_idClick')) {
            $query->where('campanha.id', $request->campanha_idClick);
        }

        $total_rows = $query->count();

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
    }

    public function create(Request $request) {
        try {

            $this->validate($request, [
                'campanha_id' => 'required|integer',
                'contato_id' => 'required|integer',
                'mensagem' => 'required|string',
                'origem_id' => 'required|integer'
            ]);

            $contato = $request->input('contato_id');
            $campanha = $request->input('campanha_id');
            $ultimo_envio = \DB::select('select max(e.id) as id
                                           from envio e,
                                        	    sequencia s,
                                                campanha c
                                          where e.sequencia_id = s.id
                                            and s.campanha_id = c.id
                                            and c.id = '. $campanha .  '
                                            and e.contato_id = ' . $contato .  ' ');

            $retorno = $request->input();
            $retorno['envio_id'] = $ultimo_envio[0]->id;

            $create = Retorno::create($retorno);

            return ['data' => $create];

        } catch (ValidationException $e) {

            $response = [];

            foreach ($e->errors() as $errors) {
                $response = array_merge($response, $errors);
            }

            return ['errors' => $response];

        }
    }

    public function update($id, Request $request) {

        $email = Retorno::find($id);

        $email->data_avaliacao = date("Y-m-d H:m:s");

        if (!$email) return [
            'errors' => ['E-mail não encontrado']
        ];

        $email->update($request->input());

        return ['data' => $email];
    }

    public function exportCSV(Request $request) {

        try{
            $user = $this->user->id;

            $query = Retorno::query()
                            ->select('contato.empresa',
                                    'contato.nome',
                                    'contato.email',
                                    'campanha.nome as campanha_nome',
                                    (\DB::raw('(concat((select ordem from sequencia_template st where st.sequencia_id = envio.sequencia_id and st.template_id = envio.template_id), "/" ,(select count(template_id) from sequencia_template st where st.sequencia_id = sequencia.id))) as cadencia')),
                                    'envio.data_bounce',
                                    'retorno.data_criacao as data',
                                    'retorno.mensagem',
                                    (\DB::raw('(CASE WHEN retorno.ind_avaliacao = 2 THEN "Neutra"
                                                     WHEN retorno.ind_avaliacao = 3 THEN "Com interesse"
                                                     WHEN retorno.ind_avaliacao = 4 THEN "Sem interesse"
                                                     ELSE "Sem Classificação" END) AS classificacao')))
                            ->join('envio', 'retorno.envio_id', '=', 'envio.id')
                            ->join('contato', 'envio.contato_id', '=', 'contato.id')
                            ->join('sequencia', 'envio.sequencia_id', '=', 'sequencia.id')
                            ->join('campanha', 'sequencia.campanha_id', '=', 'campanha.id')
                            ->join('template', 'envio.template_id', '=', 'template.id')
                            ->join('origem', 'retorno.origem_id', '=', 'origem.id')
                            ->where('contato.usuario_id', $user)
                            ->orderBy('retorno.data_criacao', 'desc');

                
            if ($request->has('campanha_id') && $request->input('campanha_id') != '') {
                $query->where('sequencia.campanha_id', '=', $request->input('campanha_id'));
            }

            if ($request->has('data_inicio') && $request->input('data_inicio') != '') {
                $query->whereDate('retorno.data_criacao', '>=', $request->input('data_inicio'));
            }

            if ($request->has('data_fim') && $request->input('data_fim') != '') {
                $query->whereDate('retorno.data_criacao', '<=', $request->input('data_fim'));
            }

            if ($request->has('empresa') && $request->input('empresa') != '') {
                $query->where('contato.empresa', 'like', '%' . $request->input('empresa') . '%');
            }

            if ($request->has('ind_aval') && $request->input('ind_aval') != '') {

                if ($request->input('ind_aval') == 1) {
                    $query->where('retorno.ind_avaliacao', '=', null);
                } else {
                    $query->where('retorno.ind_avaliacao', '=', $request->input('ind_aval'));
                }
            }

            if ($request->has('grupos_contatos') && $request->input('grupos_contatos') != '') {
              $query->where('contato.grupo_contato_id', '=', $request->input('grupos_contatos'));
            }

            if($request->has('campanha_idClick')) {
                $query->where('campanha.id', $request->campanha_idClick);
            }

            $contatos = $query->get();

            $header = [
                    'empresa',
                    'nome',
                    'email',
                    'campanha do retorno',
                    'cadência',
                    'data bounce',
                    'data retorno',
                    'conteúdo retorno',
                    'classificação'
                ];

            $output = fopen("php://output",'w') or die("Can't open php://output");

            header("Content-Type:application/csv");
            header("Content-Disposition:attachment;filename=contatos_climb.csv");

            fputcsv($output, $header);

            foreach($contatos as $contato) {
                fputcsv($output, (array) $contato->getAttributes());
            }

            fclose($output) or die("Can't close php://output");

        }catch (\Exception $e) {
            die("Ocorreu um erro ao gerar o arquivo CSV.");
        }

    }

}
