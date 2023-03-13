<?php

namespace App\Http\Controllers;

use App\Contato;
use App\Campanha;
use App\Bloqueio;
use App\Cargo;
use App\Departamento;
use App\Profissao;
use App\GrupoContato;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

class ContatoController extends Controller
{

    private $user = null;

    public function __construct() {
        $this->user = Auth::user();
    }

    public function getAll(Request $request)
    {
        try {

            $query = $this->user->contatos();
            $query->select('contato.id',
                           'contato.usuario_id',
                           'contato.nome',
                           'contato.empresa',
                           'contato.cpf_cnpj',
                           'contato.email',
                           'contato.telefone',
                           'contato.cargo_id',
                           'contato.departamento_id',
                           'contato.profissao_id',
                           'contato.grupo_contato_id',
                           'contato.facebook_link',
                           'contato.linkedin_link',
                           'contato.instagram_link',
                           'contato.twitter_link',
                           'contato.data_criacao',
                           'contato.data_atualizacao',
                           \DB::raw('(select count(*) from bloqueio where contato.id = bloqueio.contato_id and data_inativacao is null) as bloqueios_ativos'),
                           \DB::raw('(select count(*) from bloqueio where contato.id = bloqueio.contato_id and data_inativacao is not null) as bloqueios_inativos')
                           );

            if($request->has('exibir_contatos') && $request->input('exibir_contatos') == 'com-bloqueio') {
               $query->whereExists(function ($query) {
                   $query->select(\DB::raw(1))
                   ->from('bloqueio')
                   ->whereRaw('bloqueio.contato_id = contato.id')
                   ->whereNull('data_inativacao');
                });
            }

            /**
             * @Author Amanda Toretti
             * Um contato pode estar em ambos os filtros caso seja inserido em mais de uma campanha caso ele não tenha retorno em uma delas
             */
            if($request->has('exibir_contatos') && $request->input('exibir_contatos') == 'sem-retorno') {
                $query->whereRaw(' contato.id IN (SELECT contatos_retorno.contato from (SELECT envio.contato_id contato, envio.sequencia_id, (select COUNT(*) from sequencia_template where envio.sequencia_id = sequencia_template.sequencia_id) qtd_emails, count(*) total_envio FROM envio WHERE 1=1 AND not EXISTS( SELECT 1 FROM retorno r WHERE r.envio_id = envio.id) GROUP BY envio.contato_id, envio.sequencia_id HAVING total_envio = qtd_emails) as contatos_retorno)');
            }

            if($request->has('exibir_contatos') && $request->input('exibir_contatos') == 'com-retorno') {
                   $query->whereRaw(' contato.id IN (SELECT envio.contato_id FROM envio INNER JOIN retorno ON envio.id = retorno.envio_id WHERE EXISTS( SELECT 1 FROM retorno WHERE retorno.envio_id = envio.id))');

            }

            if ($request->has('data_inicial_criacao') && $request->input('data_inicial_criacao') != '') {
                $query->whereDate('data_criacao', '>=', $request->input('data_inicial_criacao'));
            }

            if ($request->has('data_final_criacao') && $request->input('data_final_criacao') != '') {
                $query->whereDate('data_criacao', '<=', $request->input('data_final_criacao'));
            }

            if ($request->has('nome') && $request->input('nome') != '') {
                $query->where('nome', 'like', '%' . $request->input('nome') . '%');
            }

            if ($request->has('cpf_cnpj') && $request->input('cpf_cnpj') != '') {
                $query->where('cpf_cnpj', 'like', '%' . $request->input('cpf_cnpj') . '%');
            }

            if ($request->has('email') && $request->input('email') != '') {
                $query->where('email', 'like', '%' . $request->input('email') . '%');
            }

            if ($request->has('cargos') && $request->input('cargos') != '') {
                $query->whereIn('cargo_id', $request->input('cargos'));
            }

            if ($request->has('campanhas') && $request->input('campanhas') != '') {
                $campanhas = $request->input('campanhas');
                $query->whereIn('contato.id', function ($query) use($campanhas){
                    $query->select('contato_id')
                    ->from('envio')
                    ->join('sequencia', 'envio.sequencia_id', '=', 'sequencia.id')
                    ->join('campanha', 'sequencia.campanha_id', '=', 'campanha.id')
                    ->whereIn('campanha.id', $campanhas);
                });
            }

            if ($request->has('departamentos') && $request->input('departamentos') != '') {
                $query->whereIn('departamento_id', $request->input('departamentos'));
            }

            if ($request->has('profissoes') && $request->input('profissoes') != '') {
                $query->whereIn('profissao_id', $request->input('profissoes'));
            }

            if ($request->has('grupos_contatos') && $request->input('grupos_contatos') != '') {
                $query->whereIn('grupo_contato_id', $request->input('grupos_contatos'));
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

            return response()->json([
                'data' => $query->get(),
                'totalRows' => $total_rows
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'errors' => [$e->getMessage()]
            ]);

        }

    }

    private function getValidation() {

        return [
            'nome' =>       ['required', 'string', 'max:128'],
            'empresa' =>    ['required', 'string', 'max:128'],
            'cpf_cnpj' =>    ['nullable', 'string', 'max:14'],
            'email' =>      ['required', 'email', 'max:128'],
            'telefone' =>   ['nullable', 'string', 'max:128'],
            'cargo_id' =>   ['nullable', 'numeric', 'digits_between:1,20'],
            'departamento_id' =>   ['nullable', 'numeric', 'digits_between:1,20'],
            'profissao_id' =>   ['nullable', 'numeric', 'digits_between:1,20'],
            'grupo_contato_id' =>   ['nullable', 'numeric', 'digits_between:1,20'],
            'facebook_link' =>   ['nullable', 'string', 'max:128'],
            'linkedin_link' =>   ['nullable', 'string', 'max:128'],
            'instagram_link' =>   ['nullable', 'string', 'max:128'],
            'twitter_link' =>   ['nullable', 'string', 'max:128'],
        ];

    }

    public function create(Request $request) {

        try {

            $this->validate($request, $this->getValidation());
            $contato = Contato::findByContatoEmail(Auth::user()->id, $request->email);

            if($contato) {
                return ['errors' => ['Email' => ['Endereço de e-mail já cadastrado']]];
            };

        } catch (ValidationException | Exception $e) {

            return ['errors' => $e->errors()];

        }

        if(!$this->validar_cnpj($request->input('cpf_cnpj')) && !$this->validar_cpf($request->input('cpf_cnpj')) && $request->input('cpf_cnpj') != ''){
            return ['errors' => ['cpf_cnpj' => ['Campo CNPJ/CPF inválido']]];
        }

        $contato = $this->user->contatos()->create($request->input());

        return ['data' => $contato];
    }

    public function update($id, Request $request) {

        $contato = Contato::find($id);

        if (!$contato) return [
            'errors' => ['Contato não encontrado']
        ];

        if(!$this->validar_cnpj($request->input('cpf_cnpj')) && !$this->validar_cpf($request->input('cpf_cnpj')) && $request->input('cpf_cnpj') != ''){
            return ['errors' => ['cpf_cnpj' => ['Campo CNPJ/CPF inválido']]];
        }

        $contato->update($request->input());

        return ['data' => $contato];
    }

    public function delete(Request $request, $id) {

        $contato = $this->user->contatos()->find($id);

        try{
            $contato->delete();
        }catch(\Exception $e){
            return ['data' => false];
        }

        return ['data' => true];
    }

    public function getContatoCampanha(Request $request) {

        $campanha_id = $request->input('campanha_id');

        $query = Contato::query()
                        ->select('contato.id',
                                \DB::raw("CONCAT(contato.nome, ' - ', contato.empresa) AS contato_empresa"))
                        ->orderBy('contato_empresa', 'asc')
                        ->where('contato.usuario_id', $this->user->id)
                        ->whereExists( function ($query) use($campanha_id){
                                        $query
                                        ->select(\DB::raw(1))
                                        ->from('envio')
                                        ->join('sequencia', 'envio.sequencia_id', '=', 'sequencia.id')
                                        ->whereRaw('envio.contato_id = contato.id')
                                        ->where('sequencia.campanha_id', $campanha_id);
                        })
                        ->whereNotExists( function ($query) use($campanha_id) {
                                            $query
                                            ->select(\DB::raw(1))
                                            ->from('retorno')
                                            ->join('envio','retorno.envio_id','=', 'envio.id')
                                            ->join('sequencia', 'envio.sequencia_id', '=', 'sequencia.id')
                                            ->whereRaw('envio.contato_id = contato.id')
                                            ->where('sequencia.campanha_id', $campanha_id);
                                        });

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

    public function getContatosSemEnvioCampanha(Request $request) {

        $query = Campanha::query()
                         ->select('contato.nome',
                         'contato.empresa',
                         'contato.email')
                         ->leftJoin('campanha_cargo', 'campanha.id', 'campanha_cargo.campanha_id')
                         ->leftJoin('campanha_grupo_contato', 'campanha.id', 'campanha_grupo_contato.campanha_id')
                         ->leftJoin('campanha_profissao', 'campanha.id', 'campanha_profissao.campanha_id')
                         ->leftJoin('campanha_departamento', 'campanha.id', 'campanha_departamento.campanha_id')
                         ->join('contato', function ($join) {
                             $join->WhereRaw('(campanha_cargo.cargo_id IS null or contato.cargo_id = campanha_cargo.cargo_id)')
                             ->WhereRaw('(campanha_grupo_contato.grupo_contato_id IS null or contato.grupo_contato_id = campanha_grupo_contato.grupo_contato_id)')
                             ->WhereRaw('(campanha_profissao.profissao_id IS null or contato.profissao_id = campanha_profissao.profissao_id)')
                             ->WhereRaw('(campanha_departamento.departamento_id IS null or contato.departamento_id = campanha_departamento.departamento_id)');
                         })
                         ->whereRaw('contato.usuario_id = campanha.usuario_id')
                         ->where('campanha.id', $request->campanha_idClick)
                         ->where('campanha.usuario_id', Auth::user()->id);

        if($request->has('bloqueio')) {
            $query->whereExists(function ($query) {
                $query->select(\DB::raw(1))
                ->from('bloqueio')
                ->whereRaw('bloqueio.contato_id = contato.id');
            });
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

    public function getBloqueios(request $request) {
        $user = $this->user->id;

        $query = Bloqueio::query()->select('bloqueio.id',
                                           'bloqueio.descricao',
                                           'bloqueio.usuario_id',
                                           'bloqueio.contato_id',
                                           'bloqueio.motivo_bloqueio_id',
                                           'motivo_bloqueio.descricao as motivo_bloqueio',
                                           'motivo_bloqueio.dica as dica',
                                           'bloqueio.data_inativacao',
                                           'bloqueio.data_bloqueio',
                                           'bloqueio.data_criacao',
                                           'bloqueio.data_atualizacao')
                                    ->join('motivo_bloqueio', 'motivo_bloqueio.id', 'bloqueio.motivo_bloqueio_id')
                                    ->where('usuario_id', $user)
                                    ->where('bloqueio.contato_id', '=', $request->contato_id)
                                    ->orderByRaw('bloqueio.data_inativacao is null desc')
                                    ->orderBy('bloqueio.data_bloqueio', 'desc');

        $total_rows = $query->count();

        if ($request->has('offset')) {
            $offset = $request->input('offset');
            $query->offset($offset);
        }

        if ($request->has('limit')) {
            $limit = $request->input('limit');
            $query->limit($limit);
        }

        return ['data' => $query->get(),
                'totalRows' => $total_rows];

    }

    public function deleteBloqueio(Request $request, $bloqueio_id) {

        try {

            $bloqueio = Bloqueio::find($bloqueio_id);

            $bloqueio->data_inativacao = \Carbon\Carbon::now();

            $bloqueio->save();

            return response()->json([
                'data' => true
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'errors' => [$e->getMessage()]
            ]);

        }
    }

    public function getIdByName($array, $nome) {

        $filter = $array->filter(function ($item) use($nome) {
            return $item->nome == $nome;
        })->values();

        return $filter[0]->id;

    }

    public function importCSV(Request $request) {

        try {

            \DB::beginTransaction();

            $data = [];
            $retorno = [];
            $erros = [];
            $dados_contato = [];
            $linha_contato = [];
            $contatos_inseridos = 0;
            $contatos_atualizados = 0;
            $qtd_erros = 0;

            $cargos = Cargo::all();
            $profissoes = Profissao::all();
            $departamentos = Departamento::all();
            $grupoContatos = $this->user->grupos_contato()->get();
            $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

            if($extension != 'csv') {
                $qtd_erros = 1;
                throw new \Exception("Arquivo com extensão inválida", 1);
            }

            $upload = $request->file('file');
            $filePath = $upload->getRealPath();
            $file = fopen($filePath, 'r');
            $header = fgetcsv($file, 0, ",", "\"");


            while (($columns = fgetcsv($file, 0, ",", "\"")) !== FALSE) {

                $data [] = [
                    'nome' => trim($columns[0]),
                    'empresa' => trim($columns[1]),
                    'cpf_cnpj' => trim($columns[2]),
                    'email' => trim($columns[3]),
                    'telefone' => trim($columns[4]),
                    'cargo_id' => trim($columns[5]),
                    'departamento_id' => trim($columns[6]),
                    'profissao_id' => trim($columns[7]),
                    'grupo_contato_id' => trim($columns[8]),
                    'facebook_link' => trim($columns[9]),
                    'linkedin_link' => trim($columns[10]),
                    'instagram_link' => trim($columns[11]),
                    'twitter_link' => trim($columns[12])
                ];

            }

            foreach ($data as $key => $object) {

                $dados_contato = [
                    'nome' => $object['nome'] ? $object['nome'] : null,
                    'empresa' => $object['empresa'] ? $object['empresa'] : null,
                    'cpf_cnpj' => $object['cpf_cnpj'] ? $object['cpf_cnpj'] : null,
                    'email' => $object['email'] ? $object['email'] : null,
                    'telefone' => $object['telefone'] ? $object['telefone'] : null,
                    'cargo_id' => $object['cargo_id'] ? $object['cargo_id'] : null,
                    'departamento_id' => $object['departamento_id'] ? $object['departamento_id'] : null,
                    'profissao_id' => $object['profissao_id'] ? $object['profissao_id'] : null,
                    'grupo_contato_id' => $object['grupo_contato_id'] ? $object['grupo_contato_id'] : null,
                    'facebook_link' => $object['facebook_link'] ? $object['facebook_link'] : null,
                    'linkedin_link' => $object['linkedin_link'] ? $object['linkedin_link'] : null,
                    'instagram_link' => $object['instagram_link'] ? $object['instagram_link'] : null,
                    'twitter_link' => $object['twitter_link'] ? $object['twitter_link'] : null,
                ];

                try {

                    $validation = \Validator::make($dados_contato, [
                        'nome' =>       ['required', 'string', 'max:128'],
                        'empresa' =>    ['required', 'string', 'max:128'],
                        'cpf_cnpj' =>    ['nullable', 'string', 'max:14'],
                        'email' =>      ['required', 'email', 'max:128'],
                        'telefone' =>   ['nullable', 'string', 'max:128'],
                        'cargo_id' =>   ['nullable', 'exists:cargo,nome'],
                        'departamento_id' =>   ['nullable', 'exists:departamento,nome'],
                        'profissao_id' =>   ['nullable', 'exists:profissao,nome'],
                        'grupo_contato_id' =>   ['nullable', 'exists:grupo_contato,nome'],
                        'facebook_link' =>   ['nullable', 'string', 'max:128'],
                        'linkedin_link' =>   ['nullable', 'string', 'max:128'],
                        'instagram_link' =>   ['nullable', 'string', 'max:128'],
                        'twitter_link' =>   ['nullable', 'string', 'max:128'],
                    ]);

                    if ($validation->fails()){
                        if(!$this->validar_cnpj(str_pad($dados_contato['cpf_cnpj'], 14, "0", STR_PAD_LEFT)) && !$this->validar_cpf(str_pad($dados_contato['cpf_cnpj'], 11, "0", STR_PAD_LEFT)) && $dados_contato['cpf_cnpj'] != '') {
                            $erros[] = [
                                'linha' => $key+2,
                                'errors' => 'O campo cpf_cnpj deve ser um CNPJ ou CPF válido.'
                            ];
                        }
                        throw new ValidationException($validation);
                    }else if(!$this->validar_cnpj(str_pad($dados_contato['cpf_cnpj'], 14, "0", STR_PAD_LEFT)) && !$this->validar_cpf(str_pad($dados_contato['cpf_cnpj'], 11, "0", STR_PAD_LEFT)) && $dados_contato['cpf_cnpj'] != ''){
                        $qtd_erros++;
                        $erros[] = [
                            'linha' => $key+2,
                            'errors' => 'O campo cpf_cnpj deve ser um CNPJ ou CPF válido.'
                        ];

                        continue;
                    }

                    if($this->validar_cnpj(str_pad($dados_contato['cpf_cnpj'], 14, "0", STR_PAD_LEFT)))
                        $dados_contato['cpf_cnpj'] = str_pad($dados_contato['cpf_cnpj'], 14, "0", STR_PAD_LEFT);

                    if($this->validar_cpf(str_pad($dados_contato['cpf_cnpj'], 11, "0", STR_PAD_LEFT)))
                        $dados_contato['cpf_cnpj'] = str_pad($dados_contato['cpf_cnpj'], 11, "0", STR_PAD_LEFT);

                    $dados_contato['cargo_id'] ? $dados_contato['cargo_id'] = $this->getIdByName($cargos, $dados_contato['cargo_id']) : null;
                    $dados_contato['departamento_id'] ? $dados_contato['departamento_id'] = $this->getIdByName($departamentos, $dados_contato['departamento_id']) : null;
                    $dados_contato['profissao_id'] ? $dados_contato['profissao_id'] = $this->getIdByName($profissoes, $dados_contato['profissao_id']) : null;
                    $dados_contato['grupo_contato_id'] ? $dados_contato['grupo_contato_id'] = $this->getIdByName($grupoContatos, $dados_contato['grupo_contato_id']) : null;

                } catch (ValidationException $e) {

                    $qtd_erros++;
                    $mensagens = [];

                    foreach ($e->errors() as $e) {

                        $mensagens = join(' ', $e);

                    }

                    $erros[] = [
                        'linha' => $key+2,
                        'errors' => $mensagens
                    ];

                    continue;

                }

                try {

                    $contato = Contato::findByContatoEmail(Auth::user()->id, $dados_contato['email']);

                    if($contato) {

                        $contato->update($dados_contato);
                        $contatos_atualizados++;

                    } else {

                        $contato = $this->user->contatos()->create($dados_contato);
                        $contato->save();
                        $contatos_inseridos++;

                    }

                } catch (\Exception $e) {

                    $qtd_erros++;

                    foreach ($e->errors() as $e) {

                        $mensagens = join(' ', $e);

                    }

                    $erros[] = [
                        'linha' => $key+2,
                        'errors' => $mensagens
                    ];

                }

            }

            if($qtd_erros == 0) {
                \DB::commit();
            } else {
                \DB::rollback();
                $contatos_inseridos = 0;
                $contatos_atualizados = 0;
            }

            return [
                'data' => [
                    'qtd_inseridos' => $contatos_inseridos,
                    'qtd_atualizados' => $contatos_atualizados,
                    'qtd_errors' => $qtd_erros,
                    'errors' => $erros,
                    'extensao' => $extension
                ]
            ];

        } catch (\Exception $e) {

            \DB::rollback();
            $contatos_inseridos = 0;
            $contatos_atualizados = 0;

            return [
                'data' => [
                    'qtd_inseridos' => $contatos_inseridos,
                    'qtd_atualizados' => $contatos_atualizados,
                    'qtd_errors' => $qtd_erros,
                    'errors' => $erros,
                    'extensao' => $extension
                ]
            ];

        }

    }

    public function exportCSV(Request $request) {

        try{
            $query = $this->user->contatos();
            $query->select('contato.nome',
                           'contato.empresa',
                           'contato.cpf_cnpj',
                           'contato.email',
                           'contato.telefone',
                           'cargo.nome as cargo',
                           'departamento.nome as departamento',
                           'profissao.nome as profissao',
                           'grupo_contato.nome as grupo_contato',
                           'contato.facebook_link',
                           'contato.linkedin_link',
                           'contato.instagram_link',
                           'contato.twitter_link')
            ->leftJoin('cargo', 'contato.cargo_id', '=', 'cargo.id')
            ->leftJoin('departamento', 'contato.departamento_id', '=', 'departamento.id')
            ->leftJoin('profissao', 'contato.profissao_id', '=', 'profissao.id')
            ->leftJoin('grupo_contato', 'contato.grupo_contato_id', '=', 'grupo_contato.id');

            if($request->has('exibir_contatos') && $request->input('exibir_contatos') == 'com-bloqueio') {
               $query->whereExists(function ($query) {
                   $query->select(\DB::raw(1))
                   ->from('bloqueio')
                   ->whereRaw('bloqueio.contato_id = contato.id')
                   ->whereNull('data_inativacao');
               });
            }

            if($request->has('exibir_contatos') && $request->input('exibir_contatos') == 'sem-retorno') {
                $query->whereRaw(' contato.id IN (SELECT contatos_retorno.contato from (SELECT envio.contato_id contato, envio.sequencia_id, (select COUNT(*) from sequencia_template where envio.sequencia_id = sequencia_template.sequencia_id) qtd_emails, count(*) total_envio FROM envio WHERE 1=1 AND not EXISTS( SELECT 1 FROM retorno r WHERE r.envio_id = envio.id) GROUP BY envio.contato_id, envio.sequencia_id HAVING total_envio = qtd_emails) as contatos_retorno)');
            }

            if($request->has('exibir_contatos') && $request->input('exibir_contatos') == 'com-retorno') {
               $query->whereRaw(' contato.id IN (SELECT envio.contato_id FROM envio INNER JOIN retorno ON envio.id = retorno.envio_id WHERE EXISTS( SELECT 1 FROM retorno WHERE retorno.envio_id = envio.id))');
            }

            if ($request->has('data_inicial_criacao') && $request->input('data_inicial_criacao') != '') {
                $query->whereDate('contato.data_criacao', '>=', $request->input('data_inicial_criacao'));
            }

            if ($request->has('data_final_criacao') && $request->input('data_final_criacao') != '') {
                $query->whereDate('contato.data_criacao', '<=', $request->input('data_final_criacao'));
            }

            if ($request->has('nome') && $request->input('nome') != '') {
                $query->where('contato.nome', 'like', '%' . $request->input('nome') . '%');
            }

            if ($request->has('cpf_cnpj') && $request->input('cpf_cnpj') != '') {
                $query->where('cpf_cnpj', 'like', '%' . $request->input('cpf_cnpj') . '%');
            }

            if ($request->has('email') && $request->input('email') != '') {
                $query->where('email', 'like', '%' . $request->input('email') . '%');
            }

            if ($request->has('cargos') && $request->input('cargos') != '') {
                $query->whereIn('cargo_id', $request->input('cargos'));
            }

            if ($request->has('campanhas') && $request->input('campanhas') != '') {
                $campanhas = $request->input('campanhas');
                $query->whereIn('contato.id', function ($query) use($campanhas){
                    $query->select('contato_id')
                    ->from('envio')
                    ->join('sequencia', 'envio.sequencia_id', '=', 'sequencia.id')
                    ->join('campanha', 'sequencia.campanha_id', '=', 'campanha.id')
                    ->whereIn('campanha.id', $campanhas);
                });
            }

            if ($request->has('departamentos') && $request->input('departamentos') != '') {
                $query->whereIn('departamento_id', $request->input('departamentos'));
            }

            if ($request->has('profissoes') && $request->input('profissoes') != '') {
                $query->whereIn('profissao_id', $request->input('profissoes'));
            }

            if ($request->has('grupos_contatos') && $request->input('grupos_contatos') != '') {
                $query->whereIn('grupo_contato_id', $request->input('grupos_contatos'));
            }

            $contatos = $query->get();

            $header = [
                    'nome',
                    'empresa',
                    'cpf_cnpj',
                    'email',
                    'telefone',
                    'cargo',
                    'departamento',
                    'profissao',
                    'grupo_contato',
                    'facebook_link',
                    'linkedin_link',
                    'instagram_link',
                    'twitter_link'
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

    public function exportSpotter(Request $request) {

        try{
            $query = $this->user->contatos();
            $query->select('contato.nome',
                           'contato.empresa',
                           'contato.cpf_cnpj',
                           'contato.email',
                           'contato.telefone',
                           'cargo.nome as cargo',
                           'departamento.nome as departamento',
                           'profissao.nome as profissao',
                           'grupo_contato.nome as grupo_contato',
                           'contato.facebook_link',
                           'contato.linkedin_link',
                           'contato.instagram_link',
                           'contato.twitter_link')
            ->leftJoin('cargo', 'contato.cargo_id', '=', 'cargo.id')
            ->leftJoin('departamento', 'contato.departamento_id', '=', 'departamento.id')
            ->leftJoin('profissao', 'contato.profissao_id', '=', 'profissao.id')
            ->leftJoin('grupo_contato', 'contato.grupo_contato_id', '=', 'grupo_contato.id');

            if($request->has('exibir_contatos') && $request->input('exibir_contatos') == 'com-bloqueio') {
               $query->whereExists(function ($query) {
                   $query->select(\DB::raw(1))
                   ->from('bloqueio')
                   ->whereRaw('bloqueio.contato_id = contato.id')
                   ->whereNull('data_inativacao');
               });
            }

            if($request->has('exibir_contatos') && $request->input('exibir_contatos') == 'sem-retorno') {
                $query->whereRaw(' contato.id IN (SELECT contatos_retorno.contato from (SELECT envio.contato_id contato, envio.sequencia_id, (select COUNT(*) from sequencia_template where envio.sequencia_id = sequencia_template.sequencia_id) qtd_emails, count(*) total_envio FROM envio WHERE 1=1 AND not EXISTS( SELECT 1 FROM retorno r WHERE r.envio_id = envio.id) GROUP BY envio.contato_id, envio.sequencia_id HAVING total_envio = qtd_emails) as contatos_retorno)');
            }

            if($request->has('exibir_contatos') && $request->input('exibir_contatos') == 'com-retorno') {
               $query->whereRaw(' contato.id IN (SELECT envio.contato_id FROM envio INNER JOIN retorno ON envio.id = retorno.envio_id WHERE EXISTS( SELECT 1 FROM retorno WHERE retorno.envio_id = envio.id))');
            }

            if ($request->has('data_inicial_criacao') && $request->input('data_inicial_criacao') != '') {
                $query->whereDate('contato.data_criacao', '>=', $request->input('data_inicial_criacao'));
            }

            if ($request->has('data_final_criacao') && $request->input('data_final_criacao') != '') {
                $query->whereDate('contato.data_criacao', '<=', $request->input('data_final_criacao'));
            }

            if ($request->has('nome') && $request->input('nome') != '') {
                $query->where('contato.nome', 'like', '%' . $request->input('nome') . '%');
            }

            if ($request->has('cpf_cnpj') && $request->input('cpf_cnpj') != '') {
                $query->where('cpf_cnpj', 'like', '%' . $request->input('cpf_cnpj') . '%');
            }

            if ($request->has('email') && $request->input('email') != '') {
                $query->where('email', 'like', '%' . $request->input('email') . '%');
            }

            if ($request->has('cargos') && $request->input('cargos') != '') {
                $query->whereIn('cargo_id', $request->input('cargos'));
            }

            if ($request->has('campanhas') && $request->input('campanhas') != '') {
                $campanhas = $request->input('campanhas');
                $query->whereIn('contato.id', function ($query) use($campanhas){
                    $query->select('contato_id')
                    ->from('envio')
                    ->join('sequencia', 'envio.sequencia_id', '=', 'sequencia.id')
                    ->join('campanha', 'sequencia.campanha_id', '=', 'campanha.id')
                    ->whereIn('campanha.id', $campanhas);
                });
            }

            if ($request->has('departamentos') && $request->input('departamentos') != '') {
                $query->whereIn('departamento_id', $request->input('departamentos'));
            }

            if ($request->has('profissoes') && $request->input('profissoes') != '') {
                $query->whereIn('profissao_id', $request->input('profissoes'));
            }

            if ($request->has('grupos_contatos') && $request->input('grupos_contatos') != '') {
                $query->whereIn('grupo_contato_id', $request->input('grupos_contatos'));
            }

            $contatos = $query->get();

            $header = [
                    'Nome da Empresa',
                    'Origem',
                    'Sub-Origem',
                    'Mercado',
                    'Produto',
                    'Site',
                    'País',
                    'Estado',
                    'Cidade',
                    'Logradouro',
                    'Numero',
                    'Bairro',
                    'Complemento',
                    'CEP',
                    'Telefone',
                    'Telefone 2',
                    'Observação',
                    'Nome Contato',
                    'E-mail Contato',
                    'Cargo Contato',
                    'Tel. Contato',
                    'Tel.2 Contato',
                    'LinkedIn Contato',
                    'Tipo do Serv. Comunicação',
                    'ID do Serv. Comunicação',
                    'CNPJ',
                ];

            $writer = WriterEntityFactory::createXLSXWriter('..\storage\export\contatos_climb_spotter.xlsx');

            $writer->openToBrowser('..\storage\export\contatos_climb_spotter.xlsx');
            $writer->addRow(WriterEntityFactory::createRowFromArray($header));

            foreach($contatos as $contato) {
                $array_contato = [
                    'Nome da Empresa' => $contato->empresa,
                    'Origem' => 'Prospecção Ativa',
                    'Sub-Origem' => '',
                    'Mercado' => $contato->grupo_contato,
                    'Produto' => '',
                    'Site' => '',
                    'País' => '',
                    'Estado' => '',
                    'Cidade' => '',
                    'Logradouro' => '',
                    'Numero' => '',
                    'Bairro' => '',
                    'Complemento' => '',
                    'CEP' => '',
                    'Telefone' => '',
                    'Telefone 2' => '',
                    'Observação' => '',
                    'Nome Contato' => $contato->nome,
                    'E-mail Contato' => $contato->email,
                    'Cargo Contato' => $contato->cargo,
                    'Tel. Contato' => $contato->telefone,
                    'Tel.2 Contato' => '',
                    'LinkedIn Contato' => $contato->linkedin_link,
                    'Tipo do Serv.Comunicação' => '',
                    'ID d Serv.Comunicação' => '',
                    'CNPJ' => $contato->cpf_cnpj,
                ];
                $row = WriterEntityFactory::createRowFromArray($array_contato);
                $writer->addRow($row);
            }

            $writer->close();

        }catch (\Exception $e) {
            return ['vrf'=>false, 'msg'=>'Não foi possível gerar o arquivo 😢', 'error'=>$e->getMessage()];
        }
    }

    public function validar_cpf($cpf) {

        if(empty($cpf))
		      return false;

    	$cpf = preg_replace('/[^0-9]/', '', $cpf);

    	if (strlen($cpf) != 11)
    		return false;

    	if (preg_match('/(\d)\1{10}/', $cpf))
    		return false;

    	for ($t = 9; $t < 11; $t++) {

    		for ($d = 0, $c = 0; $c < $t; $c++) {
    			$d += $cpf{$c} * (($t + 1) - $c);
    		}

    		$d = ((10 * $d) % 11) % 10;

    		if ($cpf{$c} != $d) {
    			return false;
    		}
    	}

    	return true;
    }

    public function validar_cnpj($cnpj) {

    	if(empty($cnpj))
    		return false;

    	$cnpj = preg_replace('/[^0-9]/', '', $cnpj);

    	if (strlen($cnpj) != 14)
    		return false;

    	if (preg_match('/(\d)\1{13}/', $cnpj))
    		return false;

    	$b = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        for ($i = 0, $n = 0; $i < 12; $n += $cnpj[$i] * $b[++$i]);

        if ($cnpj[12] != ((($n %= 11) < 2) ? 0 : 11 - $n)) {
            return false;
        }

        for ($i = 0, $n = 0; $i <= 12; $n += $cnpj[$i] * $b[$i++]);

        if ($cnpj[13] != ((($n %= 11) < 2) ? 0 : 11 - $n)) {
            return false;
        }

    	return true;
    }
}
