<?php

namespace App\Http\Controllers;

use App\Estoque;
use App\Produto;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class EstoqueController extends Controller
{

    private $user = null;

    public function __construct() {
        $this->user = Auth::user();
    }

    public function getAll(Request $request)
    {

        try {

            $query = $this->user->estoque()
            ->select('estoque.id', 'nome', 'estoque.quantidade')
            ->join('produto', 'estoque.produto_id', '=', 'produto.id');

            if ($request->has('produto_id') && $request->input('produto_id') != '') {
                $query->where('produto_id', 'like', '%' . $request->input('produto_id') . '%');
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
            'quantidade'        => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'produto_id'        => ['required', 'exists:produto,id'],
        ];
    }

    public function create(Request $request) {
        try {
            $this->validate($request, $this->getValidation());

            $estoque = $this->user->estoque()->create($request->input());

            $produto = Produto::find($request->input('produto_id'));
            $produto->quantidade = $produto->quantidade + $request->input('quantidade');
            $produto->save();

            return ['data' => $estoque];

        } catch (ValidationException | Exception $e) {

            return ['errors' => $e->errors()];

        }
    }

    public function update($id, Request $request) {
        try {
            $this->validate($request, $this->getValidation());

            $estoque = Estoque::find($id);

            if (!$estoque) return [
                'errors' => ['Estoque não encontrado']
            ];
            $estoque->update($request->input());

            return ['data' => $estoque];

        } catch (ValidationException | Exception $e) {

            return ['errors' => $e->errors()];
        }
    }

    public function delete(Request $request, $id) {

        $estoque = $this->user->estoque()->find($id);

        try{            
            $produto = Produto::find($estoque->produto_id);
            $produto->quantidade = $produto->quantidade - $estoque->quantidade;
            $estoque->delete();
            $produto->save();
        }catch(\Exception $e){
            return ['data' => false];
        }

        return ['data' => true];
    }
}
