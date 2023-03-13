<?php

namespace App\Http\Controllers;

use App\GrupoContato;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class GrupoContatoController extends Controller
{

    private $user = null;

    public function __construct() {
        $this->user = Auth::user();
    }

    public function getAll(Request $request)
    {

        try {

            $query = $this->user->grupos_contato();

            if ($request->has('nome') && $request->input('nome') != '') {
                $query->where('nome', 'like', '%' . $request->input('nome') . '%');
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
            'nome' =>       ['required', 'string', 'max:256'],
        ];
    }

    public function create(Request $request) {
        try {
            $this->validate($request, $this->getValidation());

            $exists = GrupoContato::findByGrupoContatoNome(Auth::user()->id, $request->input('nome'));
            if ($exists) {
                throw ValidationException::withMessages(['exists' => 'Grupo de contato já cadastrado 😢']);
            }
            $grupoContato = $this->user->grupos_contato()->create($request->input());
            return ['data' => $grupoContato];

        } catch (ValidationException | Exception $e) {

            return ['errors' => $e->errors()];

        }
    }

    public function update($id, Request $request) {
        try {
            $this->validate($request, $this->getValidation());

            $exists = GrupoContato::findByGrupoContatoNome(Auth::user()->id, $request->input('nome'));
            if ($exists) {
                throw ValidationException::withMessages(['exists' => 'Já existe um Grupo de Contato com este nome 😢']);
            }
            $grupoContato = GrupoContato::find($id);

            if (!$grupoContato) return [
                'errors' => ['Grupo de contato não encontrado']
            ];
            $grupoContato->update($request->input());

            return ['data' => $grupoContato];

        } catch (ValidationException | Exception $e) {

            return ['errors' => $e->errors()];
        }
    }

    public function delete(Request $request, $id) {

        $grupoContato = $this->user->grupos_contato()->find($id);

        try{
            $grupoContato->delete();
        }catch(\Exception $e){
            return ['data' => false];
        }

        return ['data' => true];
    }
}
