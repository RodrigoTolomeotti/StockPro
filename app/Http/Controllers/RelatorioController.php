<?php

namespace App\Http\Controllers;

use App\Relatorio;
use App\Envio;
use App\Retorno;
use App\Origem;
use App\Campanha;
use App\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RelatorioController extends Controller
{

    private $user = null;

    public function __construct() {
        $this->user = Auth::user();
    }

    public function getAll (request $request){

        $date = date('Y-m-d');

        $todasOrigens = Origem::select('id', 'nome');

        $envios = Envio::query()
                        ->select('origem.id',
                                'origem.nome',
                                (\DB::raw("COUNT(*) as envios")))
                        ->join('contato', 'envio.contato_id', 'contato.id')
                        ->join('sequencia', 'envio.sequencia_id', 'sequencia.id')
                        ->join('campanha', 'sequencia.campanha_id', 'campanha.id')
                        ->join('origem', 'campanha.origem_id', 'origem.id')
                        ->where('envio.data_criacao','>=', $date)
                        ->where('contato.usuario_id', $this->user->id)
                        ->groupBy('origem.id', 'origem.nome');

        $visualizados = Envio::query()
                        ->select('origem.id',
                                'origem.nome',
                                (\DB::raw("COUNT(*) as visualizados")))
                        ->join('contato', 'envio.contato_id', 'contato.id')
                        ->join('sequencia', 'envio.sequencia_id', 'sequencia.id')
                        ->join('campanha', 'sequencia.campanha_id', 'campanha.id')
                        ->join('origem', 'campanha.origem_id', 'origem.id')
                        ->where('envio.data_abertura','>=', $date)
                        ->where('contato.usuario_id', $this->user->id)
                        ->groupBy('origem.id', 'origem.nome');

        $retornos = Retorno::query()
                        ->select('origem.id',
                                'origem.nome',
                                (\DB::raw("COUNT(*) as retornos")))
                        ->join('envio', 'retorno.envio_id', 'envio.id')
                        ->join('contato', 'envio.contato_id', 'contato.id')
                        ->join('origem', 'retorno.origem_id', 'origem.id')
                        ->where('retorno.data_criacao','>=', $date)
                        ->where('contato.usuario_id', $this->user->id)
                        ->groupBy('origem.id', 'origem.nome');

        $campanhas = Campanha::query()
                    ->select('id',
                            'nome',
                            (\DB::raw("(select COUNT(*) FROM campanha ca
                                    	LEFT OUTER JOIN campanha_cargo cg ON ca.id = cg.campanha_id
                                    	LEFT OUTER JOIN campanha_grupo_contato cgc ON ca.id = cgc.campanha_id
                                    	LEFT OUTER JOIN campanha_profissao cp ON ca.id = cp.campanha_id
                                    	LEFT OUTER JOIN campanha_departamento cd ON ca.id = cd.campanha_id
                                    	inner join contato co ON (
                                            (
                                                (cg.cargo_id IS null OR cg.cargo_id = co.cargo_id) AND
                                                (cgc.grupo_contato_id IS null OR cgc.grupo_contato_id = co.grupo_contato_id) AND
                                                (cp.profissao_id IS null OR cp.profissao_id = co.profissao_id) AND
                                                (cd.departamento_id IS null OR cd.departamento_id = co.departamento_id)
                                            ) AND
                                            co.usuario_id = ca.usuario_id
                                    	)
                                    WHERE ca.id = campanha.id) as contatos")),
                            (\DB::raw("(select COUNT(*) FROM campanha ca
                                    	LEFT OUTER JOIN campanha_cargo cg ON ca.id = cg.campanha_id
                                    	LEFT OUTER JOIN campanha_grupo_contato cgc ON ca.id = cgc.campanha_id
                                    	LEFT OUTER JOIN campanha_profissao cp ON ca.id = cp.campanha_id
                                    	LEFT OUTER JOIN campanha_departamento cd ON ca.id = cd.campanha_id
                                    	inner join contato co ON (
                                            (
                                                (cg.cargo_id IS null OR cg.cargo_id = co.cargo_id) AND
                                                (cgc.grupo_contato_id IS null OR cgc.grupo_contato_id = co.grupo_contato_id) AND
                                                (cp.profissao_id IS null OR cp.profissao_id = co.profissao_id) AND
                                                (cd.departamento_id IS null OR cd.departamento_id = co.departamento_id)
                                            ) AND
                                            co.usuario_id = ca.usuario_id
                                    	)
                                    WHERE ca.id = campanha.id
                                    AND exists (select 1 from bloqueio WHERE bloqueio.contato_id = co.id)) as bloqueios")),
                            (\DB::raw("(SELECT COUNT(*) FROM envio, sequencia WHERE sequencia.campanha_id = campanha.id AND sequencia.id = envio.sequencia_id) as envios")),
                            (\DB::raw("(SELECT COUNT(*) FROM envio, sequencia WHERE sequencia.campanha_id = campanha.id AND sequencia.id = envio.sequencia_id AND envio.data_abertura is not null) as abertos")),
                            (\DB::raw("(SELECT COUNT(*) FROM retorno, envio, sequencia WHERE sequencia.campanha_id = campanha.id AND sequencia.id = envio.sequencia_id AND envio.id = retorno.envio_id) as respostas")))
                        ->where('usuario_id', Auth::user()->id)
                        ->orderBy('nome');

        if($request->has('statusCampanha') && $request->input('statusCampanha') != '') {
            if($request->input('statusCampanha') == 1) {
                $campanhas->whereNull('campanha.data_inativacao');
            } else if($request->input('statusCampanha') == 2) {
                $campanhas->whereNotNull('campanha.data_inativacao');
            }
        }

        $dados = [
            'envios' => $envios->get(),
            'visualizados' => $visualizados->get(),
            'retornos' => $retornos->get(),
            'origem' => $todasOrigens->get(),
            'campanhas' => $campanhas->get()
        ];

        return ['data' => $dados];


    }

}
