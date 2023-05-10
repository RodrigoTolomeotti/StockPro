<?php

namespace App\Http\Controllers;

use App\TipoProduto;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class TipoProdutoController extends Controller
{

    private $user = null;

    public function __construct() {
        $this->user = Auth::user();
    }

    public function getAll(Request $request)
    {

        try {

            $query = $this->user->tiposProdutos();

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

            $exists = TipoProduto::findByTipoProdutoNome(Auth::user()->id, $request->input('nome'));
            if ($exists) {
                throw ValidationException::withMessages(['exists' => 'Tipo de Produto já cadastrado 😢']);
            }
            $tipoProduto = $this->user->tiposProdutos()->create($request->input());
            return ['data' => $tipoProduto];

        } catch (ValidationException | Exception $e) {

            return ['errors' => $e->errors()];

        }
    }

    public function update($id, Request $request) {
        try {
            $this->validate($request, $this->getValidation());

            $exists = TipoProduto::findByTipoProdutoNome(Auth::user()->id, $request->input('nome'));
            if ($exists) {
                throw ValidationException::withMessages(['exists' => 'Já existe um Tipo de Produto com este nome 😢']);
            }
            $tipoProduto = TipoProduto::find($id);

            if (!$tipoProduto) return [
                'errors' => ['Tipo de Produto não encontrado']
            ];
            $tipoProduto->update($request->input());

            return ['data' => $tipoProduto];

        } catch (ValidationException | Exception $e) {

            return ['errors' => $e->errors()];
        }
    }

    public function delete(Request $request, $id) {

        $tipoProduto = $this->user->tiposProdutos()->find($id);

        try{
            $tipoProduto->delete();
        }catch(\Exception $e){
            return ['data' => false];
        }

        return ['data' => true];
    }
}
