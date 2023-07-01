<?php

namespace App\Http\Controllers;

use App\itemPedido;
use App\Pedido;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class itemPedidoController extends Controller
{

    private $user = null;

    public function __construct() {
        $this->user = Auth::user();
    }

    public function getAllIItems(Request $request) 
    {
        try {
            $pedido = Pedido::find($request->input('pedido_id'));
            dd($pedido->itensPedido()->limit(1));

        } catch (\Exception $e) {

            return response()->json([
                'errors' => [$e->getMessage()]
            ]);

        }
    }

    public function getAll(Request $request)
    {

        try {

            $itemsPedido = Pedido::find($request->input('pedido_id'))->itensPedido();

            if ($request->has('nome') && $request->input('nome') != '') {
                $itemsPedido->where('nome', 'like', '%' . $request->input('nome') . '%');
            }
            
            if ($request->has('offset')) {
                $offset = $request->input('offset');
                $itemsPedido->offset($offset);
            }
            
            if ($request->has('limit')) {
                $limit = $request->input('limit');
                $itemsPedido->limit($limit);
            }
            
            if ($request->has('sort_by')) {
                $sort_by = $request->input('sort_by');
                $order_by = $request->input('order_by', 'asc');
                $itemsPedido->orderBy($sort_by, $order_by);
            }
            
            $total_rows = $itemsPedido->count();
            
            $itemsPedido = $itemsPedido->get();
            
            return response()->json([
                'data' => $itemsPedido,
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
            // 'pedido_id'        => ['required', 'exists:pedido,id'],
            'produto_id'       => ['required', 'exists:produto,id'],
            'preco_unitario'   => ['required', 'numeric', 'min:1', 'regex:/^\d+(\.\d{1,2})?$/'],
            'desconto'         => ['nullable', 'numeric', 'min:1', 'regex:/^\d+(\.\d{1,2})?$/'],
            'quantidade'       => ['nullable', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
        ];
    }

    public function create(Request $request) {
        try {
            $object = $request->input();
            if(!$object['desconto']) {
                $object['desconto'] = 0;
            }
            if(!$object['quantidade']) {
                $object['quantidade'] = 0;
            }
            // dd($object);
            $this->validate($request, $this->getValidation());

            if(!$request->input('pedido_id'))
                throw ValidationException::withMessages(['save' => 'Salve o cabeçalho do pedido!']);

            $pedido = $this->user->itensPedido()->create($object);
            return ['data' => $pedido];

        } catch (ValidationException | Exception $e) {

            return ['errors' => $e->errors()];

        }
    }

    public function update($id, Request $request) {
        try {
            $this->validate($request, $this->getValidation());

            $pedidos = itemPedido::find($id);

            if (!$pedidos) return [
                'errors' => ['Pedido não encontrado']
            ];
            $pedidos->update($request->input());

            return ['data' => $pedidos];

        } catch (ValidationException | Exception $e) {

            return ['errors' => $e->errors()];
        }
    }

    public function delete(Request $request, $id) {

        $pedidos = $this->user->itensPedido()->find($id);

        try{
            $pedidos->delete();
        }catch(\Exception $e){
            return ['data' => false];
        }

        return ['data' => true];
    }
}
