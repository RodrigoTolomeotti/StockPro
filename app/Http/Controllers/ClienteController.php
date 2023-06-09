<?php

namespace App\Http\Controllers;

use App\Cliente;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{

    private $user = null;

    public function __construct() {
        $this->user = Auth::user();
    }

    public function getAll(Request $request)
    {

        try {

            $query = $this->user->clientes();

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
            'nome'      =>  ['required', 'string', 'max:256'],
            'cpf_cnpj'  =>  ['required', 'string', 'max:14'],
            'telefone'  =>  ['nullable', 'numeric', 'digits_between:1,11'],
            'email'     =>  ['required', 'email:rfc,dns', 'max:256', 'unique:cliente']
            
        ];
    }

    public function create(Request $request) {
        try {
            $this->validate($request, $this->getValidation());

            if(!validar_cnpj($request->input('cpf_cnpj')) && !validar_cpf($request->input('cpf_cnpj'))){
                return ['errors' => ['cpf_cnpj' => ['Campo CPF/CNPJ inválido']]];
            }
            $clientes = $this->user->clientes()->create($request->input());
            return ['data' => $clientes];

        } catch (ValidationException | Exception $e) {

            return ['errors' => $e->errors()];

        }
    }

    public function update($id, Request $request) {
        try {
            $this->validate($request, $this->getValidation());

            $clientes = Cliente::find($id);

            if (!$clientes) return [
                'errors' => ['Cliente não encontrado']
            ];
            $clientes->update($request->input());

            return ['data' => $clientes];

        } catch (ValidationException | Exception $e) {

            return ['errors' => $e->errors()];
        }
    }

    public function delete(Request $request, $id) {

        $clientes = $this->user->clientes()->find($id);

        try{
            $clientes->delete();
        }catch(\Exception $e){
            return ['data' => false];
        }

        return ['data' => true];
    }
}
