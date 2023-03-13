<?php

namespace App\Http\Controllers;

use App\Campanha;
use App\Sequencia;
use App\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class CampanhaController extends Controller
{

    private $user = null;

    public function __construct() {
        $this->user = Auth::user();
    }

    private function getValidation($request) {

        if ($request->input('origem_id') == 1) {

            return [
                'id' => [
                    'nullable',
                    'numeric',
                    'digits_between:1,20'
                ],
                'nome' => [
                    'required',
                    'string',
                    'max:128'
                ],
                'data_inicio' => [
                    'required',
                    'date'
                ],
                'cargos.*' => [
                    'required',
                    'exists:cargo,id'
                ],
                'departamentos.*' => [
                    'required',
                    'exists:departamento,id'
                ],
                'profissoes.*' => [
                    'required',
                    'exists:profissao,id'
                ],
                'grupos_contato.*' => [
                    'required',
                    Rule::exists('grupo_contato', 'id')->where(function ($query) {
                        $query->where('usuario_id', $this->user->id);
                    })
                ],
                'emails' => [
                    'required',
                    'array'
                ],
                'emails.*.id' => [
                    'nullable',
                    'exists:template,id'
                ],
                'emails.*.encaminhar' => [
                    'nullable',
                    'boolean'
                ],
                'emails.*.dias_enviar' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:1825'
                ],
                'emails.*.dias_semana' => [
                    'required',
                    'string',
                    'min:1',
                    'max:13'
                ],
                'emails.*.hora_inicial' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:23'
                ],
                // 'emails.*.hora_final' => [
                //     'required',
                //     'integer',
                //     'min:0',
                //     'max:23'
                // ],
                'emails.*.assunto' => [
                    'required',
                    'string',
                    'max:128'
                ],
                'emails.*.mensagem' => [
                    'required',
                    'string'
                ]
            ];

        } else if ($request->input('origem_id') == 6) {
            return [
                'id' => [
                    'nullable',
                    'numeric',
                    'digits_between:1,20'
                ],
                'nome' => [
                    'required',
                    'string',
                    'max:128'
                ],
                'data_inicio' => [
                    'required',
                    'date'
                ],
                'cargos.*' => [
                    'required',
                    'exists:cargo,id'
                ],
                'departamentos.*' => [
                    'required',
                    'exists:departamento,id'
                ],
                'profissoes.*' => [
                    'required',
                    'exists:profissao,id'
                ],
                'grupos_contato.*' => [
                    'required',
                    Rule::exists('grupo_contato', 'id')->where(function ($query) {
                        $query->where('usuario_id', $this->user->id);
                    })
                ],
                'mensagensWhatsapp' => [
                    'required',
                    'array'
                ],
                'mensagensWhatsapp.*.id' => [
                    'nullable',
                    'exists:template,id'
                ],
                'mensagensWhatsapp.*.encaminhar' => [
                    'nullable',
                    'boolean'
                ],
                'mensagensWhatsapp.*.dias_enviar' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:1825'
                ],
                'mensagensWhatsapp.*.dias_semana' => [
                    'required',
                    'string',
                    'min:1',
                    'max:13'
                ],
                'mensagensWhatsapp.*.hora_inicial' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:23'
                ],
                // 'mensagensWhatsapp.*.hora_final' => [
                //     'required',
                //     'integer',
                //     'min:0',
                //     'max:23'
                // ],
                // 'mensagensWhatsapp.*.assunto' => [
                //     'required',
                //     'string',
                //     'max:128'
                // ],
                'mensagensWhatsapp.*.mensagem' => [
                    'required',
                    'string'
                ]
            ];
        }
    }

    public function getAll(Request $request)
    {
        try {

            $query = $this->user
                          ->campanhas()
                          ->with('cargos')
                          ->with('departamentos')
                          ->with('profissoes')
                          ->with('grupos_contato')
                          ->with(['sequencia.templates' => function($query) {
                              $query->orderBy('ordem', 'ASC');
                          }]);

            if ($request->has('nome') && $request->input('nome') != '') {
                $query->where('nome', 'like', '%' . $request->input('nome') . '%');
            }

            if ($request->has('data_inicio') && $request->input('data_inicio') != '') {
                $query->whereDate('data_inicio', $request->input('data_inicio'));
            }

            if ($request->has('status') && $request->input('status') != '') {
                switch ($request->input('status')) {
                    case 1:
                        $query->whereNull('data_inativacao');
                    break;
                    case 2:
                        $query->whereNotNull('data_inativacao');
                    break;
                }
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

            $campanhas = $query->get();

            foreach ($campanhas as $key => $campanha) {
                $possui_envio = \DB::select("SELECT 1
                                                  FROM campanha c
                                            INNER JOIN sequencia s ON c.id = s.campanha_id
                                            INNER JOIN envio e ON s.id = e.sequencia_id
                                                   AND c.id = $campanha->id");
                if ($possui_envio) {
                    $campanha['mensagensEnviadas'] = 1;
                } else {
                    $campanha['mensagensEnviadas'] = 0;
                }
            }
            return response()->json([
                'data' => $campanhas,
                'totalRows' => $total_rows
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'errors' => [$e->getMessage()]
            ]);

        }

    }

    public function create(Request $request) {

        $data = $request->input();

        try {

            $this->validate($request, $this->getValidation($request));

        } catch (ValidationException | Exception $e) {

            return ['errors' => $e->errors()];

        }

        $cargos = $data['cargos'];
        $departamentos = $data['departamentos'];
        $profissoes = $data['profissoes'];
        $grupos_contato = $data['grupos_contato'];
        $emails = $data['emails'];
        $mensagensWhatsapp = $data['mensagensWhatsapp'];

        unset($data['cargos']);
        unset($data['departamentos']);
        unset($data['profissoes']);
        unset($data['grupos_contato']);
        unset($data['emails']);
        unset($data['mensagensWhatsapp']);



        $campanha = $this->user->campanhas()->create($data);

        foreach ($cargos as $cargo) $campanha->cargos()->attach($cargo);
        foreach ($departamentos as $departamento) $campanha->departamentos()->attach($departamento);
        foreach ($profissoes as $profissao) $campanha->profissoes()->attach($profissao);
        foreach ($grupos_contato as $grupo) $campanha->grupos_contato()->attach($grupo);

        $sequencia = $campanha->sequencias()->create();

        $mensagens = '';

        if ($data['origem_id'] == 1) {
            $mensagens = $emails;
        } elseif ($data['origem_id'] == 6) {
            $mensagens = $mensagensWhatsapp;
        };

        foreach ($mensagens as $key => $mensagem) {
            $template = new Template($mensagem);
            $sequencia->templates()->save($template, ['ordem' => $key + 1]);
        };

        $campanha->sequencia()->associate($sequencia);
        $campanha->save();

        return ['data' => $campanha];
    }

    public function update($id, Request $request) {

        $campanha = $this->user->campanhas()->find($id);

        if (!$campanha) return [
            'errors' => ['Campanha não encontrado']
        ];

        try {

            $data = $this->validate($request, $this->getValidation($request));

            $cargos = $data['cargos'];
            $departamentos = $data['departamentos'];
            $profissoes = $data['profissoes'];
            $grupos_contato = $data['grupos_contato'];
            $emails = $data['emails'];
            $mensagensWhatsapp = $data['mensagensWhatsapp'];
            $origem = $request->input('origem_id');

            unset($data['cargos']);
            unset($data['departamentos']);
            unset($data['profissoes']);
            unset($data['grupos_contato']);
            unset($data['emails']);
            unset($data['mensagensWhatsapp']);

        } catch (ValidationException | Exception $e) {

            return ['errors' => $e->errors()];

        }

        $campanha->update($data);

        $campanha->cargos()->detach();
        $campanha->departamentos()->detach();
        $campanha->profissoes()->detach();
        $campanha->grupos_contato()->detach();

        foreach ($cargos as $cargo) $campanha->cargos()->attach($cargo);
        foreach ($departamentos as $departamento) $campanha->departamentos()->attach($departamento);
        foreach ($profissoes as $profissao) $campanha->profissoes()->attach($profissao);
        foreach ($grupos_contato as $grupo) $campanha->grupos_contato()->attach($grupo);

        $nova_sequencia = false;
        $mensagens = '';

        if ($origem == 1) {
            $mensagens = $emails;
        } elseif ($origem == 6) {
            $mensagens = $mensagensWhatsapp;
        };


        if (count($campanha->sequencia->templates) != count($mensagens)) $nova_sequencia = true;

        if (!$nova_sequencia) {
            foreach ($mensagens as $i => $mensagem) {
                if ((isset($mensagem['change']) && $mensagem['change']) || !isset($mensagem['id'])) {
                    $nova_sequencia = true;
                    break;
                }
            };
        }

        if ($nova_sequencia) {

            $sequencia = $campanha->sequencias()->create();

            foreach ($mensagens as $key => $mensagem) {

                if ((isset($mensagem['change']) && $mensagem['change']) || !isset($mensagem['id'])) {

                    if (isset($mensagem['id'])) unset($mensagem['id']);

                    $template = new Template($mensagem);
                    $sequencia->templates()->save($template, ['ordem' => $key + 1]);

                } else {

                    $sequencia->templates()->attach($mensagem['id'], ['ordem' => $key + 1]);

                }

            };

            $campanha->sequencia()->associate($sequencia);
            $campanha->save();

        }

        return ['data' => $campanha];

    }

    public function ativar($id, Request $request)
    {
        try {

            $campanha = $this->user->campanhas()->find($id);

            $campanha->data_inativacao = null;

            $campanha->save();

            return response()->json([
                'data' => true
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'errors' => [$e->getMessage()]
            ]);

        }

    }

    public function inativar($id, Request $request)
    {
        try {

            $campanha = $this->user->campanhas()->find($id);

            $campanha->data_inativacao = \Carbon\Carbon::now();

            $campanha->save();

            return response()->json([
                'data' => true
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'errors' => [$e->getMessage()]
            ]);

        }

    }

}
