<?php

namespace App\Http\Controllers;

use App\Fornecedor;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class FornecedorController extends Controller
{

    private $user = null;

    public function __construct() {
        $this->user = Auth::user();
    }

    public function getAll(Request $request)
    {

        try {

            $query = $this->user->fornecedores();

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
			'nome'			=>      ['required', 'string', 'max:256'],
			'telefone'		=>      ['required', 'numeric', 'digits_between:1,14'],
			'email'			=>      ['required', 'email', 'max:256', 'regex:/(.+)@(.+)\.(.+)/i'],
			'cpf_cnpj'		=>      ['required', 'max:14'],
        ];
    }

    public function create(Request $request) {
        try {
            $this->validate($request, $this->getValidation());

            $exists = Fornecedor::findByFornecedorNome(Auth::user()->id, $request->input('nome'));
            if ($exists) {
                throw ValidationException::withMessages(['exists' => 'Fornecedor já cadastrado 😢']);
            }

            if(!validar_cnpj($request->input('cpf_cnpj')) && !validar_cpf($request->input('cpf_cnpj'))){
                return ['errors' => ['cpf_cnpj' => ['Campo CPF/CNPJ inválido']]];
            }

            $fornecedor = $this->user->fornecedores()->create($request->input());
            return ['data' => $fornecedor];

        } catch (ValidationException | Exception $e) {

            return ['errors' => $e->errors()];

        }
    }

    public function update($id, Request $request) {
        try {
            $this->validate($request, $this->getValidation());

            $exists = Fornecedor::findByFornecedorNome(Auth::user()->id, $request->input('nome'));
            if ($exists) {
                throw ValidationException::withMessages(['exists' => 'Já existe um Fornecedor com este nome 😢']);
            }
            $fornecedor = Fornecedor::find($id);

            if (!$fornecedor) return [
                'errors' => ['Fornecedor não encontrado']
            ];
            $fornecedor->update($request->input());

            return ['data' => $fornecedor];

        } catch (ValidationException | Exception $e) {

            return ['errors' => $e->errors()];
        }
    }

    public function delete(Request $request, $id) {

        $fornecedor = $this->user->fornecedores()->find($id);

        try{
            $fornecedor->delete();
        }catch(\Exception $e){
            return ['data' => false];
        }

        return ['data' => true];
    }
}
